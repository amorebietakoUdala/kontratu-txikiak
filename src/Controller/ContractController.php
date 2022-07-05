<?php

namespace App\Controller;

use App\Entity\Contract;
use App\Entity\IdentificationType;
use App\Entity\User;
use App\Form\ContractFormType;
use App\Form\ContractSearchFormType;
use App\Repository\ContractRepository;
use App\Repository\UserRepository;
use App\Service\ContractNotifierService;
use App\Utils\Validaciones;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Translation\TranslatableMessage;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use \Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class ContractController extends AbstractController
{

    private UserRepository $userRepo;
    public function __construct(UserRepository $userRepo)
    {
        $this->userRepo = $userRepo;
    }

    /**
     * @Route("/{_locale}/contract/new", name="app_contract_new")
     */
    public function new(Request $request, EntityManagerInterface $em, ContractRepository $repo) {
        $form = $this->createForm(ContractFormType::class, new Contract(),[
            'locale' => $request->getLocale(),
            'disabled' => false,
        ]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var Contract $data */
            $data = $form->getData();
            if ( !$this->checkForErrors($data, $repo) ) {
                $data->setUser($this->getUser());
                $em->persist($data);
                $em->flush();
                $this->addFlash('success', 'contract.saved');
                return $this->redirectToRoute('app_contract_index');
            }
        }

        return $this->renderForm('contract/edit.html.twig',[
            'form' => $form,
            'readonly' => false,
            'new' => true,
        ]);
    }

    /**
     * @Route("/{_locale}/contract/download", name="app_contract_download")
     */
    public function download(Request $request, ContractRepository $repo) {
        $form = $this->createForm(ContractSearchFormType::class, null, [
            'locale' => $request->getLocale(),
        ]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $contracts = $repo->findByAwardDateAndNotified($data['startDate'], $data['endDate'], $data['notified']);
        }
        return $this->generateSpreadSheet($contracts);
    }

    /**
     * @Route("/{_locale}/contract/{id}/edit", name="app_contract_edit")
     */
    public function edit(Request $request, Contract $contract, EntityManagerInterface $em, ContractRepository $repo) {
        $form = $this->createForm(ContractFormType::class, $contract,[
            'locale' => $request->getLocale(),
            'disabled' => false,
        ]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var Contract $data */
            $data = $form->getData();
            $contractWithSameCode = $repo->findByCode($data->getCode());
            if (null !== $contractWithSameCode && $data->getId() !== $contractWithSameCode->getId()) {
                $this->addFlash('error', new TranslatableMessage('error.duplicateCode',[
                    '{code}' => $contract->getCode(),
                ]));
                return $this->renderForm('contract/edit.html.twig',[
                    'contract' => $contract,
                    'form' => $form,
                    'readonly' => false,
                    'new' => false,
                ]);
            }
            $data->setNotified(false);
            $em->persist($data);
            $em->flush();
            $this->addFlash('success', 'contract.saved');
            return $this->redirectToRoute('app_contract_index');
        }
        return $this->renderForm('contract/edit.html.twig',[
            'contract' => $contract,
            'form' => $form,
            'readonly' => false,
            'new' => false,
        ]);
    }

    /**
     * @Route("/{_locale}/contract/{id}", name="app_contract_show")
     */
    public function show(Request $request, Contract $contract) {
        $form = $this->createForm(ContractFormType::class, $contract,[
            'locale' => $request->getLocale(),
            'disabled' => true,
        ]);

        return $this->renderForm('contract/edit.html.twig',[
            'contract' => $contract,
            'form' => $form,
            'readonly' => true,
            'new' => false,
        ]);
    }

    /**
     * @Route("/{_locale}/contract/{id}/send", name="app_contract_send")
     */
    public function send(Request $request, Contract $contract, EntityManagerInterface $em, ContractNotifierService $contractNotifierService) {
        /** @var User $user  */
        $user = $this->getUser();
        if ( null === $user->getIdNumber() ) {
            $this->addFlash('error', 'error.noUserIdNumber');
            return $this->redirectToRoute('app_contract_index');
        }        
        if ($this->isCsrfTokenValid('send'.$contract->getId(), $request->get('_token'))) {
            try {
                $response = $contractNotifierService->notify($contract, $user);
                if( $response['result'] === 'OK' ) {
                    $contract->setNotified(true);
                    $contract->setResponseId($response['id']);
                    $em->persist($contract);
                    $em->flush();
                    $this->addFlash('success','messages.successfullyNotified');
                } else {
                    $this->addFlash('error', $response['error']);
                }
                return $this->redirectToRoute('app_contract_index');
            } catch (HttpExceptionInterface $e) {
                $this->addFlash('error',$e->getMessage());
            }
        }
    }

    /**
     * @Route("/{_locale}/contract/{id}/delete", name="app_contract_delete")
     */
    public function delete(Request $request, Contract $contract, EntityManagerInterface $em) {
        $page = $request->get('page') ? $request->get('page') : 1;
        $pageSize = $request->get('pageSize') ? $request->get('pageSize') : 1;
        if ($this->isCsrfTokenValid('delete'.$contract->getId(), $request->get('_token'))) {
            $em->remove($contract);
            $em->flush();
            $this->addFlash('success','contract.deleted');
        } else {
            $this->addFlash('error','error.invalidCsrfToken');
        }       
        return $this->redirectToRoute('app_contract_index', [
            'page' => $page,
            'pageSize' => $pageSize,
        ]);
    }

    /**
     * @Route("/{_locale}/contract", name="app_contract_index")
     */
    public function index(Request $request, ContractRepository $repo): Response
    {
        $page = $request->query->get('page') ?  $request->query->get('page') : 1;
        $pageSize = $request->query->get('pageSize') ?  $request->query->get('pageSize') : 10;
        $data = [];
        if ($request->getMethod() === Request::METHOD_GET) {
            if ($request->getSession()->get('contracts') === null) {
                $contracts = $repo->findBy([],['createdAt'=>'DESC'],50);
            } else {
                $contracts = $request->getSession()->get('contracts');
                $data = $request->getSession()->get('data');
                $data['user'] = $this->userRepo->find($data['user']);
            }
            if (count($contracts) === 50) {
                $this->addFlash('warning', 'messages.maxResultsReached');
            }
        }
        $form = $this->createForm(ContractSearchFormType::class, $data, [
            'locale' => $request->getLocale(),
        ]);
        $form->handleRequest($request);
        if ( $form->isSubmitted() && $form->isValid() ) {
            /** @var array $data */
            $data = $form->getData();
            $contracts = $repo->findByAwardDateAndNotified($data['startDate'], $data['endDate'], $data['notified'], $data['user']);
            $request->getSession()->set('contracts', $contracts);
            $request->getSession()->set('data', $data);
        }

        return $this->renderForm('contract/index.html.twig', [
            'contracts' => $contracts,
            'form' => $form,
            'page' => $page,
            'pageSize' => $pageSize,
        ]);
    }

    /**
     * @Route("/", name="app_home")
     */
    public function home() {
        return $this->redirectToRoute('app_contract_index');
    }

    private function checkForErrors(Contract $contract, ContractRepository $repo) {
        if ( null !== $repo->findByCode($contract->getCode()) ) {
            $this->addFlash('error', new TranslatableMessage('error.duplicateCode',[
                '{code}' => $contract->getCode(),
            ]));
            return true;
        }
        if ( $contract->getDuration() > 99999 ) {
            $this->addFlash('error', 'error.invalidDuration');
            return true;
        }
        if ( null !== $contract->getAmountWithoutVAT() && null !== $contract->getAmountWithVAT() && $contract->getAmountWithoutVAT() > $contract->getAmountWithVAT() ) {
            $this->addFlash('error', new TranslatableMessage('error.amountWithoutVATGreatherThanamountWithVAT'));
            return true;
        }
        if ( null !== $contract->getType()->getMaxAmount() && $contract->getAmountWithoutVAT() > $contract->getType()->getMaxAmount() ) {
            $this->addFlash('error', new TranslatableMessage('error.exceededMaxAmountForType', [
                '{maxAmount}' => $contract->getType()->getMaxAmount(),
            ]));
            return true;
        }
        if ( $contract->getAwardDate() > new \DateTime() ) {
            $this->addFlash('error', 'error.invalidAwardDate');
            return true;
        }
        if ( $contract->getIdentificationType()->getId() === IdentificationType::IDENTIFICATION_TYPE_CIF && Validaciones::valida_nif_cif_nie($contract->getIdNumber()) !== 2 ) {
            $this->addFlash('error', new TranslatableMessage('error.cifNotValid', [
                '{cif}' => $contract->getIdNumber(),
            ]));
            return true;
        }
        if ( $contract->getIdentificationType()->getId() === IdentificationType::IDENTIFICATION_TYPE_NIF && Validaciones::valida_nif_cif_nie($contract->getIdNumber()) !== 1 ) {
            $this->addFlash('error', new TranslatableMessage('error.nifNotValid', [
                '{nif}' => $contract->getIdNumber(),
            ]));
            return true;
        }
        return false;
    }

    /** 
     * @param Contract[] $contracts 
     * 
     * @return Response
     * */
    private function generateSpreadSheet(array $contracts) {
        $rootDir = $this->getParameter('kernel.project_dir');
        $inputFileName = $rootDir.'/src/Resources/PlantillaCargaMenores.xlsx';
        $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
        $spreadsheet = $reader->load($inputFileName);
        $startRow = 5;
        $sheet = $spreadsheet->getActiveSheet();
        foreach ($contracts as $contract) {
            $this->fillContract($sheet,$startRow, $contract);
            $startRow++;
        }
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->setPreCalculateFormulas(false);
        $fileName = 'contracts.xlsx';
        $writer->save($rootDir."/public/downloads/$fileName");

        $response = new Response(null, 200,[
            'Content-Disposition' => "attachment; filename=\"$fileName\"",
            "Content-Type" => 'application/vnd.ms-excel',
        ]);
        
        return $response->setContent(file_get_contents($rootDir."/public/downloads/$fileName"));        
    }

    private function fillContract(Worksheet &$sheet, int $startRow, Contract $contract) {
        $startColumn = 'A';
        $sheet->setCellValue($startColumn.$startRow, $contract->getCode());
        $sheet->setCellValue((++$startColumn).$startRow, $contract->getType());
        $sheet->setCellValue((++$startColumn).$startRow, $contract->getSubjectEs());
        $sheet->setCellValue((++$startColumn).$startRow, $contract->getSubjectEu());
        $sheet->setCellValue((++$startColumn).$startRow, '');
        $sheet->setCellValue((++$startColumn).$startRow, $contract->getAmountWithoutVAT());
        $sheet->setCellValue((++$startColumn).$startRow, $contract->getAmountWithVAT());
        $sheet->setCellValue((++$startColumn).$startRow, '');
        $sheet->setCellValue((++$startColumn).$startRow, $contract->getDurationType());
        $sheet->setCellValue((++$startColumn).$startRow, $contract->getDuration());
        $sheet->setCellValue((++$startColumn).$startRow, $contract->getIdentificationType());
        $sheet->setCellValue((++$startColumn).$startRow, $contract->getIdNumber());
        $sheet->setCellValue((++$startColumn).$startRow, $contract->getEnterprise());
        $sheet->setCellValue((++$startColumn).$startRow, $contract->getAwardDate()->format('d/m/Y'));
        $sheet->setCellValue((++$startColumn).$startRow, '');
        return $sheet;
    }
}
