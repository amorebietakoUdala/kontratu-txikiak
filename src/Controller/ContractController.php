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
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Translation\TranslatableMessage;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use \Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use \PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class ContractController extends AbstractController
{

    public function __construct(
        private readonly UserRepository $userRepo, 
        private readonly ContractRepository $contractRepo,
        private readonly EntityManagerInterface $em,
        )
    {
    }

    #[Route(path: '/{_locale}/contract/new', name: 'app_contract_new')]
    public function new(Request $request, ContractRepository $repo) {
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
                $this->em->persist($data);
                $this->em->flush();
                $this->addFlash('success', 'contract.saved');
                return $this->redirectToRoute('app_contract_index');
            }
        }

        return $this->render('contract/edit.html.twig',[
            'form' => $form,
            'readonly' => false,
            'new' => true,
        ]);
    }

    #[Route(path: '/{_locale}/contract/download', name: 'app_contract_download')]
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

    #[Route(path: '/{_locale}/contract/{id}/edit', name: 'app_contract_edit')]
    public function edit(Request $request, #[MapEntity(id: 'id')] Contract $contract, ContractRepository $repo) {
        $returnUrl = $request->query->get('returnUrl');
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
                return $this->render('contract/edit.html.twig',[
                    'contract' => $contract,
                    'form' => $form,
                    'readonly' => false,
                    'new' => false,
                ]);
            }
            $data->setNotified(false);
            $this->em->persist($data);
            $this->em->flush();
            $this->addFlash('success', 'contract.saved');
            if ( null !== $returnUrl ) {
                return $this->redirect($returnUrl);
            }
            return $this->redirectToRoute('app_contract_index');
        }
        return $this->render('contract/edit.html.twig',[
            'contract' => $contract,
            'form' => $form,
            'readonly' => false,
            'new' => false,
        ]);
    }

    #[Route(path: '/{_locale}/contract/{id}', name: 'app_contract_show')]
    public function show(Request $request, #[MapEntity(id: 'id')]  Contract $contract) {
        $form = $this->createForm(ContractFormType::class, $contract,[
            'locale' => $request->getLocale(),
            'disabled' => true,
        ]);

        return $this->render('contract/edit.html.twig',[
            'contract' => $contract,
            'form' => $form,
            'readonly' => true,
            'new' => false,
        ]);
    }

    #[Route(path: '/{_locale}/contract/{id}/send', name: 'app_contract_send')]
    public function send(Request $request, #[MapEntity(id: 'id')] Contract $contract,  ContractNotifierService $contractNotifierService) {
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
                    $contract->setRawResponse($response['raw']);
                    $this->em->persist($contract);
                    $this->em->flush();
                    $this->addFlash('success','messages.successfullyNotified');
                } else {
                    $contract->setRawResponse($response['raw']);
                    $this->em->persist($contract);
                    $this->em->flush();
                    $this->addFlash('error', $response['error']);
                }
                return $this->redirectToRoute('app_contract_index',[
                    'refresh' => true,
                    'page' => $request->query->get('page'),
                    'pageSize' => $request->query->get('pageSize'),
                    'sortName' => $request->query->get('sortName'),
                    'sortOrder' => $request->query->get('sortOrder'),
                ]);
            } catch (HttpExceptionInterface $e) {
                $this->addFlash('error',$e->getMessage());
            }
        }
    }

    #[IsGranted("ROLE_ADMIN")]
    #[Route(path: '/{_locale}/contract/{id}/mark-as-sent', name: 'app_contract_mark_as_sent')]
    public function markAsSent(Request $request, #[MapEntity(id: 'id')] Contract $contract) {
        if ($this->isCsrfTokenValid('mark_as_sent'.$contract->getId(), $request->get('_token'))) {
            $contract->setNotified(true);
            $this->em->persist($contract);
            $this->em->flush();
        } else {
            $this->addFlash('error','error.invalidCsrfToken');
        }
        return $this->redirectToRoute('app_contract_index',[
            'refresh' => true,
            'page' => $request->query->get('page'),
            'pageSize' => $request->query->get('pageSize'),
            'sortName' => $request->query->get('sortName'),
            'sortOrder' => $request->query->get('sortOrder'),
        ]);
    }

    #[Route(path: '/{_locale}/contract/{id}/delete', name: 'app_contract_delete')]
    public function delete(Request $request, #[MapEntity(id: 'id')] Contract $contract, EntityManagerInterface $em) {
        if ($this->isCsrfTokenValid('delete'.$contract->getId(), $request->get('_token'))) {
            $this->em->remove($contract);
            $this->em->flush();
            $this->addFlash('success','contract.deleted');
        } else {
            $this->addFlash('error','error.invalidCsrfToken');
        }       
        return $this->redirectToRoute('app_contract_index', [
            'refresh' => true,
            'page' => $request->query->get('page'),
            'pageSize' => $request->query->get('pageSize'),
            'sortName' => $request->query->get('sortName'),
            'sortOrder' => $request->query->get('sortOrder'),
        ]);
    }

    private function refreshContracts ($request) {
        $data = $request->getSession()->get('data');
        if ( $data['user'] !== null ) {
            $data['user'] = $this->userRepo->find($data['user']);
        }
        $request->getSession()->set('data', $data);
        $contracts = [];
        if ($request->get('refresh')) {
            $contracts = $this->contractRepo->findByAwardDateAndNotified($data['startDate'], $data['endDate'], $data['notified'], $data['user']);
        } else {
            $contracts = $request->getSession()->get('contracts');
        } 

        return $contracts;
    }

    #[Route(path: '/{_locale}/contract', name: 'app_contract_index')]
    public function index(Request $request, ContractRepository $repo): Response
    {
        $page = $request->query->get('page') ?: 1;
        $pageSize = $request->query->get('pageSize') ?: 10;
        $data = [];
        if ($request->getMethod() === Request::METHOD_GET) {
            if ($request->getSession()->get('contracts') === null) {
                $contracts = $repo->findBy([],['createdAt'=>'DESC'],50);
            } else {
                $data = $request->getSession()->get('data');
                if ( $data['user'] !== null ) {
                    $data['user'] = $this->userRepo->find($data['user']);
                }
                $request->getSession()->set('data', $data);
                $contracts = $this->refreshContracts($request);
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

        return $this->render('contract/index.html.twig', [
            'contracts' => $contracts,
            'form' => $form,
            'page' => $page,
            'pageSize' => $pageSize,
            'sortName' => $request->query->get('sortName'),
            'sortOrder' => $request->query->get('sortOrder'),
        ]);
    }

    #[Route(path: '/', name: 'app_home')]
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
        $writer = new Xlsx($spreadsheet);
        $writer->setPreCalculateFormulas(false);
        $fileName = 'contracts.xlsx';
        $writer->save($rootDir."/public/downloads/$fileName");

        $response = new Response(null, Response::HTTP_OK,[
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
