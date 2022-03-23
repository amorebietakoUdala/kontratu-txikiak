<?php

namespace App\Controller;

use App\Entity\Contract;
use App\Entity\IdentificationType;
use App\Form\ContractFormType;
use App\Form\ContractSearchFormType;
use App\Repository\ContractRepository;
use App\Utils\Validaciones;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Translation\TranslatableMessage;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ContractController extends AbstractController
{

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
    public function download(Request $request) {
        // TODO 
        $inputFileName = '/var/www/html/SF5/kontratu-txikiak/src/Resources/PlantillaCargaMenores.xlsx';
        $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
        $spreadsheet = $reader->load($inputFileName);
        $spreadsheet->getActiveSheet()->setCellValue('A7', 12345.6789);
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->setPreCalculateFormulas(false);
        $writer->save("/var/www/html/SF5/kontratu-txikiak/public/downloads/05featuredemo.xlsx");

        // Headers for download 
        $response = new Response(null, 200,[
            'Content-Disposition' => "attachment; filename=\"contracts.xlsx\"",
            "Content-Type" => 'application/vnd.ms-excel',
        ]);
        
        return $response->setContent(file_get_contents('/var/www/html/SF5/kontratu-txikiak/public/downloads/05featuredemo.xlsx'));
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
            if ($data->getId() !== $contractWithSameCode->getId()) {
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
    public function show(Request $request, Contract $contract, EntityManagerInterface $em) {
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
     * @Route("/{_locale}/contract/{id}/delete", name="app_contract_delete")
     */
    public function delete(Request $request, Contract $contract, EntityManagerInterface $em) {
        if ($this->isCsrfTokenValid('delete'.$contract->getId(), $request->get('_token'))) {
            $em->remove($contract);
            $em->flush();
            $this->addFlash('success','contract.deleted');
        } else {
            $this->addFlash('error','error.invalidCsrfToken');
        }       
        return $this->redirectToRoute('app_contract_index');
    }

    /**
     * @Route("/{_locale}/contract", name="app_contract_index")
     */
    public function index(Request $request, ContractRepository $repo): Response
    {
        $contracts = $repo->findAll();
        $form = $this->createForm(ContractSearchFormType::class, null, [
            'locale' => $request->getLocale(),
        ]);

        $form->handleRequest($request);

        if ( $form->isSubmitted() && $form->isValid() ) {
            /** @var array $data */
            $data = $form->getData();
            $contracts = $repo->findByAwardDate($data['startDate'], $data['endDate']);
        }

        return $this->renderForm('contract/index.html.twig', [
            'contracts' => $contracts,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/", name="app_home")
     */
    public function home() {
        return $this->redirectToRoute('app_contract_index');
    }

    private function removeBlankFilters($filter) {
        $criteria = [];
        foreach ( $filter as $key => $value ) {
            if (null !== $value) {
                $criteria[$key] = $value;
            }
        }
        return $criteria;
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

        if ( null !== $contract->getType()->getMaxAmount() && $contract->getAmountWithVAT() > $contract->getType()->getMaxAmount() ) {
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

}
