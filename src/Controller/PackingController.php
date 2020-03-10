<?php

namespace App\Controller;

use App\Entity\OrderInvoice;
use App\Entity\PackingBatchInvoice;
use App\Entity\Packing;
use App\Entity\PackingBatchInvoiceIncomplete;
use App\Entity\PackingBatchMaterial;
use App\Entity\PackingBatchMaterialIncomplete;
use App\Entity\PackingBatchSku;
use App\Repository\PackingBatchInvoiceInCompleteRepository;
use App\Repository\PackingBatchMaterialInCompleteRepository;
use App\Repository\PackingRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

use App\Repository\PackingBatchInvoiceRepository;
use App\Repository\BomRepository;
use App\Repository\PackingBatchSkuRepository;
use App\Repository\OrderInvoiceRepository;
use App\Repository\OrderInvoiceItemRepository;
use App\Repository\PackingBatchMaterialRepository;
use App\Repository\MaterialQtyRepository;
use App\Repository\InvoiceReserveMaterialRepository;

use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;


class PackingController extends AbstractController
{
    /**
     * @Route("/packing", name="packing")
     */
    public function index(
        Request $request
    )
    {
        return $this->render('packing/index.html.twig', []);
    }

    /**
     * @Route("/packing/info", name="packing_info")
     */
    public function packingInfo(
        Request $request,
        PaginatorInterface $paginator,
        PackingRepository $packingRepository,
        AuthorizationCheckerInterface $authChecker
    )
    {
        date_default_timezone_set("Asia/Bangkok");
        $strSearch = $request->query->get('search');
        $warehouseCode = $this->getUser()->getDefaultWarehouse()->getWarehouseCode();
        $dataList = [];
        if (true === $authChecker->isGranted('ROLE_ADMIN')) {
            $listPacking = $packingRepository->findListPackingAdmin();
        } else {
            $listPacking = $packingRepository->findListPacking($warehouseCode);
        }

        foreach ($listPacking as $key => $list) {
            $dataList[$list['packingBatchId']][] = $list;
        }
        foreach ($dataList as $key => $r) {
            $dataList[$key] = $r[0];
        }
        $resultData = array_values($dataList);
        $result = $paginator->paginate(
            $resultData,
            $request->query->getInt('page', 1),
            $request->query->getInt('limit', 10)
        );
        if ($strSearch != "") {
            $dataList2 = [];
            $packingResult = $packingRepository->findByPackingBatchId($request->query->get('search'), $warehouseCode);
            foreach ($packingResult as $key => $list) {
                $dataList2[$list['packingBatchId']][] = $list;
            }

            foreach ($dataList2 as $key => $r) {
                $dataList2[$key] = $r[0];
            }
            $resultData2 = array_values($dataList2);

            $result = $paginator->paginate(
                $resultData2,
                $request->query->getInt('page', 1)/*page number*/,
                10/*limit per page*/
            );
        }
        return $this->render('packing/listpacking.html.twig', [
            'items' => $result
        ]);
    }

    /**
     * @Route("/packing/checkPacking", name="packing_check_packing")
     */
    public function checkPacking(
        Request $request,
        PackingRepository $packingRepository
    )
    {
        $warehouseCode = $this->getUser()->getDefaultWarehouse()->getWarehouseCode();
        $packingStepObj = $packingRepository->findBy(array(
            'startPackingBatchEnd' => null,
            'warehouseCode' => $warehouseCode

        ));

        if ($packingStepObj == null) {
            $packing = new Packing();
            $packing->setPackingStep(1);
            $packing->setWarehouseCode($warehouseCode);
            $packing->setStartPackingBatchAt(new \DateTime("now", new \DateTimeZone('Asia/Bangkok')));
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($packing);
            $entityManager->flush();
            $output = array(
                'packingData' => array($packing),
                'status' => 'pass'
            );
        } else {
            $output = array(
                'packingData' => $packingStepObj,
                'status' => 'pass'
            );
        }
        return $this->json($output);
    }

    /**
     * @Route("/packing/checkInvoice", name="packing_check_invoiceCode")
     */
    public function checkInvoiceCode(
        Request $request,
        PackingBatchInvoiceRepository $packingBatchInvoiceRepository,
        PackingRepository $packingRepository,
        OrderInvoiceRepository $orderInvoiceRepository,
        MaterialQtyRepository $materialQtyRepository,
        InvoiceReserveMaterialRepository $repInvoiceReserveMaterial,
        PackingBatchInvoiceIncompleteRepository $packBatchInvoiceIncompleteRepo,
        PackingBatchMaterialInCompleteRepository $packBatchMaterialIncompleteRepo
    )
    {
        $packingBatchId = $request->request->get('packingBatchId');
        $warehouseCode = $this->getUser()->getDefaultWarehouse()->getWarehouseCode();

        $packingBatchObj = $packingRepository->findOneBy(array(
            'packingBatchId' => $packingBatchId,
            'warehouseCode' => $warehouseCode
        ));

        $packingBatchInvoiceObj = $packingBatchInvoiceRepository->count(array(
            'packingBatchId' => $packingBatchId
        ));

        $packingBatchInvoiceObjTest = $packingBatchInvoiceRepository->findBy(array(
            'packingBatchId' => $packingBatchId
        ));
//        $output = [];
        $completeData = [];
        $incompleteData = [];
        $incompleteMaterial = [];
        $count = 0;
        $availableMaterial = [];
        $entityManager = $this->getDoctrine()->getManager();

        $listMaterial = $materialQtyRepository->summaryMaterialByWarehouse($warehouseCode);//list material in their warehouse
        foreach ($listMaterial as $material) {
            $key = $material["materialCode"];
            $value = $material["materialQty"];
            $availableMaterial[$key] = $value;
        }

        $listInvoice = $orderInvoiceRepository->findInvoiceNotPacking($warehouseCode);//select all invoice in their warehouse
        if ($packingBatchInvoiceObj >= 1) {
            $output = array(
                'packingStatus' => 'pass',
                'countPackingBatchInvoice' => $packingBatchInvoiceObj,
                'testInvoice' => $packingBatchInvoiceObjTest
            );
        } else {
            if ($listInvoice == null) {
                $output = array('packingStatus' => 'fail');
            } else {
                $meet_require = true;
                foreach ($listInvoice as $invoice) {
                    //find material and quantity require in each invoice
                    $requireMaterials = $repInvoiceReserveMaterial->findMaterialReserveByInvoice($warehouseCode, $invoice["subInvoice"]);
                    foreach ($requireMaterials as $requireMaterial) {
                        $materialCode = $requireMaterial["materialCode"];
                        if (array_key_exists($materialCode, $requireMaterial)) {
                            $requireMaterial[$materialCode] += $requireMaterial["materialQty"];
                        } else {
                            $requireMaterial[$materialCode] = 0;
                            $requireMaterial[$materialCode] += $requireMaterial["materialQty"];
                        }
                    }

                    foreach ($requireMaterials as $key => $requireMaterial) {

                        $quantity = $requireMaterial["materialQty"];
                        $code = $requireMaterial["materialCode"];
//                        echo '(Material Code = '.$code .' | '.$quantity.' > '. $availableMaterial[$code].')';
                        if ($quantity > $availableMaterial[$code]) {
                            //เช็ค Invoice ที่ไม่ได้แพ็คโดย Material Code
                            $meet_require = false;
                            $incompleteData[] = $invoice["subInvoice"];
                            $incompleteMaterial[$invoice["primaryMaterialCode"]][] = $invoice["primaryMaterialQuantity"];
                        } else {
                            $meet_require = true;
                            $completeData[] = $invoice["subInvoice"];
                        }
                    }
                    if ($meet_require) {
                        foreach ($requireMaterials as $requireMaterial) {
                            $quantity = $requireMaterial["materialQty"];
                            $code = $requireMaterial["materialCode"];
                            $availableMaterial[$code] = $availableMaterial[$code] - $quantity;
                        }
                        $packingBatchInvoice = new PackingBatchInvoice();
                        $packingBatchInvoice->setPackingBatchId($packingBatchObj);
                        $packingBatchInvoice->setSubInvoice($invoice["subInvoice"]);
                        $entityManager->persist($packingBatchInvoice);
                        $entityManager->flush();
                        $count++;
                    } else {
                        // 1.รวม QTY ทั้งหมดของแต่ละ Invoice และเก็บ Invoice
                        $checkInsertInvoice_InComplete = $packBatchInvoiceIncompleteRepo->count(array(
                            'packingBatchId' => $packingBatchId, 'subInvoice' => $invoice["subInvoice"]
                        ));
                        if ($checkInsertInvoice_InComplete == 0) {
                            $invoiceInComplete = new PackingBatchInvoiceIncomplete();
                            $invoiceInComplete->setPackingBatchId($packingBatchObj);
                            $invoiceInComplete->setSubInvoice($invoice["subInvoice"]);
                            $entityManager->persist($invoiceInComplete);
                            $entityManager->flush();
                        }
                    }
                }
                // If Data No Complete
                if (!empty($incompleteMaterial)) {
                    $checkInsertMaterial_InComplete = $packBatchMaterialIncompleteRepo->count(array(
                        'packingBatchId' => $packingBatchId
                    ));
                    // 1.Check Material In Table Material By PackingBatchId
                    if ($checkInsertMaterial_InComplete == 0) {
                        // Insert to Invoice and Material Data No Complete
                        foreach ($incompleteMaterial as $key => $code) {
                            $incompleteMaterial[$key]["total"] = 0;
                            foreach ($code as $k => $v) {
                                $incompleteMaterial[$key]["total"] = $incompleteMaterial[$key]["total"] + $v;
                            }
                            $materialInComplete = new PackingBatchMaterialIncomplete();
                            $materialInComplete->setPackingBatchId($packingBatchObj);
                            $materialInComplete->setMaterialCode($key);
                            $materialInComplete->setMaterialQuantity($incompleteMaterial[$key]["total"]);
                            $entityManager->persist($materialInComplete);
                            $entityManager->flush();
                        }
                    }
                }
                $output = array(
                    'packingStatus' => 'pass',
                    'countPackingBatchInvoice' => $count,
                    'completeData' => $completeData,
                    'incompleteData' => $incompleteData
                );
            }
        }

        return $this->json($output);
    }

    /**
     * @Route("/packing/checksku", name="packing_check_sku")
     */
    public function checkSkuCode(
        Request $request,
        PackingBatchInvoiceRepository $packingBatchInvoiceRepository,
        PackingRepository $packingRepository,
        PackingBatchSkuRepository $packingBatchSkuRepository,
        EntityManagerInterface $entityManager
    )
    {
        $output = array();
        $warehouseCode = $this->getUser()->getDefaultWarehouse()->getWarehouseCode();
        $packingBatchId = $request->request->get('packingBatchId');

        $packingBatchObj = $packingRepository->findOneBy(array(
            'packingBatchId' => $packingBatchId,
            'warehouseCode' => $warehouseCode
        ));

        $countPackingBatchSku = $packingBatchSkuRepository->count(array('packingBatchId' => $packingBatchId));
        $requireListSku = $packingBatchInvoiceRepository->findSkuRequireByPackingBatchID($packingBatchObj);
        if ($countPackingBatchSku == 0) {
            foreach ($requireListSku as $requireSku) {
                $packingBatchSku = new PackingBatchSku();
                $packingBatchSku->setPackingBatchId($packingBatchObj);
                $packingBatchSku->setSkuCode($requireSku["skuCode"]);
                $packingBatchSku->setSkuQty($requireSku["skuQuantity"]);
                $entityManager->persist($packingBatchSku);
                $output = array('skuData' => $requireListSku, 'packingStatus' => 'pass');
            }
        } else {
            $output = array('skuData' => $requireListSku, 'packingStatus' => 'pass');
        }
        //Set Packing Status
        $packingObj = $packingRepository->find($packingBatchId);
        $packingObj->setPackingStep(2);
        $entityManager->flush();

        return $this->json($output);
    }

    /**
     * @Route("/packing/checkMaterial", name="packing_check_material")
     */
    public function checkMaterial(
        Request $request,
        PackingRepository $packingRepository,
        PackingBatchSkuRepository $packingBatchSkuRepository,
        PackingBatchMaterialRepository $packingBatchMaterialRepository,
        MaterialQtyRepository $materialQtyRepository,
        OrderInvoiceRepository $orderInvoiceRepository,
        EntityManagerInterface $entityManager
    )
    {
        $warehouseCode = $this->getUser()->getDefaultWarehouse()->getWarehouseCode();
        $packingBatchId = intval($request->request->get('packingBatchId'));

        // dd($packingBatchId , $warehouseCode);
        $packingBatchObj = $packingRepository->findOneBy(array(
            'packingBatchId' => $packingBatchId,
            'warehouseCode' => $warehouseCode
        ));


        //Find Material By Packing Batch Id
        $materialObj = $packingBatchSkuRepository->findSummaryMaterialByPackingBatchId($packingBatchId);

        $summaryMaterial = [];
        $calculateMaterial = [];
        $materialCodeObj = [];
        for ($j = 0; $j < count($materialObj); $j++) {
            $key = $materialObj[$j]["materialCode"];
            if (!array_key_exists($key, $summaryMaterial)) {
                $summaryMaterial[$key] = 0;
            }
            $summaryMaterial[$key] += $materialObj[$j]["quantity"] * $materialObj[$j]["skuQty"];
        }

        foreach ($summaryMaterial as $materialCode => $quantity) {
            $countPackingBatchMaterial = $packingBatchMaterialRepository->count(array(
                'materialCode' => $materialCode,
                'packingBatchId' => $packingBatchId
            ));

            $materialCodeObj[] = $materialCode;
            $materialCheckQty = $materialQtyRepository->findQueryMaterialQtyByMaterialCode($materialCode,
                $warehouseCode);

            $calculateMaterial[] = array(
                'materialQty' => $materialCheckQty[0]["materialQty"],
                'materialSumQty' => ($materialCheckQty[0]["materialQty"] - $quantity)
            );

            if ($countPackingBatchMaterial == 0) {
                $packingBatchMaterial = $this->createPackingBatchMaterial($packingBatchObj, $materialCode,
                    $quantity);
                $entityManager->persist($packingBatchMaterial);
            }
        }
        $entityManager->flush();

        //find Invoice Where Material is not enough
        $invoiceWithMaterialNotEnough = $orderInvoiceRepository->findInvoiceNotInPackingBatchInvoice($warehouseCode);

        //findQueryBillingByPackingBatchId
        $printBillingData = $packingBatchMaterialRepository->findQueryBillingByPackingBatchId($packingBatchId);

        $packingObj = $packingRepository->find($packingBatchId);
        $packingObj->setPackingStep(3);
        $entityManager->flush();

        return $this->json(array(
            'calculateMaterial' => $calculateMaterial,
            'printBillingData' => $printBillingData,
//            'invoiceRemainData' => $invoiceWithMaterialNotEnough,
            'packingStatus' => 'pass'
        ));
    }

    /**
     * @Route("/packing/setPackingStatus", name="packing_set_status")
     */
    public function setPackingStatus(
        Request $request,
        PackingRepository $packingRepository,
        OrderInvoiceRepository $orderInvoiceRepository,
        PackingBatchInvoiceRepository $packingBatchInvoiceRepository
    )
    {
        date_default_timezone_set("Asia/Bangkok");

        $status = $request->request->get('status');
        $packingBatchId = $request->request->get('packingBatchId');
        $packing = $packingRepository->find($packingBatchId);
        $entityManager = $this->getDoctrine()->getManager();
        if ($status == 'confirm') {
            $packing->setStartPackingBatchEnd(new \DateTime("now", new \DateTimeZone('Asia/Bangkok')));
            $packing->setpackingStep(7);
            $entityManager->flush();
            $output = array('status' => $status);
        } elseif ($status === 'print') {
            $packing->setpackingStep(5);
            $entityManager->flush();
            $output = array('status' => 'pass');
        } elseif ($status === 'requisition') {
            $packing->setpackingStep(6);
            $entityManager->flush();
            $output = array('status' => 'pass');
        } elseif ($status === 'finish') {
            $packing->setpackingStep(7);
            $packing->setStartPackingBatchEnd(new \DateTime("now", new \DateTimeZone('Asia/Bangkok')));
            $entityManager->flush();
            //Change Status Invoice
            $listInvoice = $orderInvoiceRepository->findOrderInvoiceByPackingBatchInvoice($packingBatchId);
            for ($i = 0; $i < count($listInvoice); $i++) {
                $listInvoice[$i]->setInvoiceStatus(1);
            }
            $entityManager->flush();

            $output = array('status' => 'pass');
        } else {
            $packing->setStartPackingBatchEnd(new \DateTime("now", new \DateTimeZone('Asia/Bangkok')));
            $packing->setpackingStep(0);
            $entityManager->flush();
            $output = array('status' => $status);
        }

        return $this->json($output);
    }

    /**
     * @Route("/packing/printbill", name="packing_print_bill")
     */
    public function printBill(
        Request $request,
        PackingBatchSkuRepository $packingBatchSkuRepository,
        PackingBatchMaterialRepository $packingBatchMaterialRepository
    )
    {
        $datetime = (new \DateTime("now", new \DateTimeZone('Asia/Bangkok')))->format('d/m/Y H:i');
        $packingBatchId = $request->query->get('packingBatchIdBill');

        // Set Sku Quantity
        $printSkuData = $packingBatchSkuRepository->findSummarySkuByPackingBatchId($packingBatchId);
        //findQueryBillingByPackingBatchId
        $printMaterialData = $packingBatchMaterialRepository->findQueryBillingByPackingBatchId($packingBatchId);

        return $this->render('packing/printbill.html.twig', [
            'materialItems' => $printMaterialData,
            'skuItems' => $printSkuData,
            'datetime' => $datetime
        ]);
    }

    /**
     * @Route("/packing/printlabel", name="packing_print_label")
     */
    public function printLabel(
        Request $request,
        PackingBatchInvoiceRepository $packingBatchInvoiceRepository
    )
    {
        $packingBatchId = $request->query->get('packingBatchIdLabel');
        //findQueryLabelByPackingBatchId
        $printLabelData = $packingBatchInvoiceRepository->findQueryLabelByPackingBatchId($packingBatchId);
        foreach ($printLabelData as $key => $val) {
            foreach ($val as $k => $v) {
                if ($k === 'orderPhoneNo') {
                    $printLabelData[$key]['orderPhoneNo'] = $this->doubleSix2Zero($v);
                }
            }
        }
        return $this->render('packing/printlabel.html.twig', [
            'labels' => $printLabelData
        ]);
    }

    /**
     * @Route("/packing/printlabel_thaipost", name="packing_print_label_thaipost")
     */
    public function printLabel_thaipost(
        Request $request,
        PackingBatchInvoiceRepository $packingBatchInvoiceRepository
    )
    {
        $packingBatchId = $request->query->get('packingBatchIdLabel');
        //findQueryLabelByPackingBatchId
        $printLabelData = $packingBatchInvoiceRepository->findQueryLabelByPackingBatchId($packingBatchId);
        foreach ($printLabelData as $key => $val) {
            foreach ($val as $k => $v) {
                if ($k === 'orderPhoneNo') {
                    $printLabelData[$key]['orderPhoneNo'] = $this->doubleSix2Zero($v);
                }
            }
        }
        return $this->render('packing/printlabel_thaipost.html.twig', [
            'labels' => $printLabelData
        ]);
    }

    public function doubleSix2Zero($phoneNO)
    {
        $pattern = '/^66\d{9}$/';
        $phoneNO = trim($phoneNO);
        if (preg_match($pattern, $phoneNO)) {
            $arr = str_split($phoneNO);
            if (isset($arr[0], $arr[1])) {
                if ($arr[0] . $arr[1] == '66') {
                    $phoneNO = '0';
                    for ($i = 2; $i < count($arr); $i++) {
                        $phoneNO .= $arr[$i];
                    }
                }
            }
        }
        return $phoneNO;
    }

    /**
     * @param Packing|null $packingBatchObj
     * @param $materialCode
     * @param $quantity
     * @return PackingBatchMaterial
     */
    public function createPackingBatchMaterial(
        ?Packing $packingBatchObj,
        $materialCode,
        $quantity
    ): PackingBatchMaterial
    {
        $packingBatchMaterial = new PackingBatchMaterial();
        $packingBatchMaterial->setPackingBatchId($packingBatchObj);
        $packingBatchMaterial->setMaterialCode($materialCode);
        $packingBatchMaterial->setMaterialQty($quantity);
        return $packingBatchMaterial;
    }

    /**
     * @Route("/packing/view", name="view_packing")
     */
    public function viewPackingInvoice(
        Request $request,
        PackingBatchInvoiceRepository $repPackingBatchInvoice
    )
    {
        $packingBatchId = $request->request->get('packingBatchId');
        $listInvoice = $repPackingBatchInvoice->findInvoiceByPackingBatchId($packingBatchId);
        $countPackingBatchInvoice = $repPackingBatchInvoice->count(array(
            'packingBatchId' => $packingBatchId
        ));
        $output = array(
            "packingBatchId" => $packingBatchId,
            "listInvoice" => $listInvoice,
            "countInvoice" => $countPackingBatchInvoice
        );
        return $this->json($output);
    }

    /**
     * @Route("/packing/incomplete/view", name="view_incomplete_packing")
     */
    public function viewPackingInvoice_InComplete(
        Request $request,
        PackingBatchInvoiceInCompleteRepository $repPackingBatchInvoice
    )
    {
        $packingBatchId = $request->request->get('packingBatchId');
        $listInvoice = $repPackingBatchInvoice->findInvoiceByPackingBatchId($packingBatchId);
        $countPackingBatchInvoice = $repPackingBatchInvoice->count(array(
            'packingBatchId' => $packingBatchId
        ));
        $output = array(
            "packingBatchId" => $packingBatchId,
            "listInvoice" => $listInvoice,
            "countInvoice" => $countPackingBatchInvoice
        );
        return $this->json($output);
    }

    /**
     * @Route("/packing/printlist", name="packing_print_list")
     */
    public function printList(
        Request $request,
        PackingBatchInvoiceRepository $packingBatchInvoiceRepository
    )
    {
        $datetime = (new \DateTime("now", new \DateTimeZone('Asia/Bangkok')))->format('d/m/Y');
        $packingBatchId = $request->query->get('packingBatchIdList');

        $printLabelData = $packingBatchInvoiceRepository->findListLabelByPackingBatchId($packingBatchId);
        foreach ($printLabelData as $key => $val) {
            foreach ($val as $k => $v) {
                if ($k === 'orderPhoneNo') {
                    $printLabelData[$key]['orderPhoneNo'] = $this->doubleSix2Zero($v);
                }
            }
        }
//        dd($printLabelData);
        return $this->render('packing/printlist.html.twig', [
            'date' => $datetime,
            'labels' => $printLabelData
        ]);
    }


}
