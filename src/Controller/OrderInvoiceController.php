<?php

namespace App\Controller;

use App\Repository\MaterialQtyRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

use Knp\Component\Pager\PaginatorInterface;

use Doctrine\DBAL\DBALException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Component\HttpClient\HttpClient;

use App\Entity\OrderInvoice;
use App\Entity\OrderInvoiceItem;
use App\Entity\OrderReceive;
use App\Entity\Bom;
use App\Entity\InvoiceReserveMaterial;
use App\Entity\MaterialQtyReserve;

use App\Repository\SkuInfoRepository;
use App\Repository\OrderInvoiceItemRepository;
use App\Repository\BomRepository;
use App\Repository\MaterialQtyReserveRepository;
use App\Repository\InvoiceReserveMaterialRepository;
use App\Repository\OrderReceiveRepository;
use App\Repository\OrderInvoiceRepository;
use App\Repository\WarehouseConfigRepository;

use App\Form\UpdatePendingType;

class OrderInvoiceController extends AbstractController
{
    /**
     * @Route("/api/order/invoice/add/post", methods={"POST"})
     */
    public function apiOrderInvoiceAddPost(
        Request $request,
        SkuInfoRepository $repSkuInfo,
        OrderInvoiceItemRepository $repOrderItem,
        MaterialQtyReserveRepository $repMaterialQtyReserve,
        InvoiceReserveMaterialRepository $repInvoiceReserveMaterial,
        OrderReceiveRepository $repOrderReserve,
        OrderInvoiceRepository $repOrderInvoice,
        WarehouseConfigRepository $repWarehouseConfig,
        MaterialQtyRepository $materialQtyRepository,
        BomRepository $repBom
    )
    {
        date_default_timezone_set("Asia/Bangkok");
        $data = json_decode($request->getContent(), true);

        // Check Invoice Receive
        $orderReceiveArray = $repOrderReserve->findInvoice($data['invoice'], $data['merchantCode']);

        if ($orderReceiveArray != null) {
            $output = 'ERROR_DUPLICATED';
            return $this->json(array('output' => $output));
        } else {
            // Save Receive JSON then insert to Entity
            $orderReceive = new OrderReceive();
            $orderReceive->setInvoice($data['invoice']);
            $orderReceive->setMerchantCode($data['merchantCode']);
            $orderReceive->setJsonRawData($request->getContent());
            $orderReceive->setReceiveDate(new \DateTime($data['transitionTimeStamp'], new \DateTimeZone('Asia/Bangkok')));
            $orderReceive->setSubInvoice($data['subInvoice']);

            $em = $this->getDoctrine()->getManager();
            $em->persist($orderReceive);
            $em->flush();

            $selectWarehouseCode = $this->checkWarehouseAvailable(
                $data['postCode'],
                $data['warehouseCode'],
                $repWarehouseConfig,
                $data['productList'],
                $materialQtyRepository,
                $repMaterialQtyReserve,
                $repBom,
                $repSkuInfo,
                $data['logisticsSelector']
            );

            $orderInvoice = new OrderInvoice();
            $orderInvoice->setWarehouseCode($selectWarehouseCode['selectWarehouse']);
            $orderInvoice->setOrderReceiveAt(new \DateTime($data['orderDate'], new \DateTimeZone('Asia/Bangkok')));
            $orderInvoice->setInvoice($data['invoice']);
            $orderInvoice->setSubInvoice($data['subInvoice']);
            $orderInvoice->setTypeTransport($data['transport']);
            $orderInvoice->setCodValue($data['codValue']);
            $orderInvoice->setOrderName($data['orderName']);
            $orderInvoice->setOrderAddress($data['orderAddress']);
            $orderInvoice->setOrderPhoneNo($data['orderPhoneNo']);
            $orderInvoice->setPostCode($data['postCode']);
            $orderInvoice->setLogisticsSelector($selectWarehouseCode['selectLogistic']);
            $orderInvoice->setOperation($data['operation']);
            $orderInvoice->setMerchantCode($data['merchantCode']);
            $orderInvoice->setTracking($data['trackingNo']);
//            $orderInvoice->setRefTrackingNo($data['trackingNo2']);
            $orderInvoice->setOrderShortNote($data['orderShortNote']);
            if ($data['orderShortNote'] == NULL) {
                $orderInvoice->setInvoiceStatus(0);
            } else {
                $orderInvoice->setInvoiceStatus(2);
            }

            $em = $this->getDoctrine()->getManager();
            $em->persist($orderInvoice);
            $em->flush();

            $products = $data['productList'];

            for ($i = 0; $i < count($products); $i++) {
                $newItem = new OrderInvoiceItem();
                $sku = $repSkuInfo->find($products[$i]['skuCode']);
                if ($sku === null) {
                    $output = 'ERROR_SKU_NOT_FOUND';
                    return $this->json(['output' => $output]);
                } else {
                    $newItem->setSkuCode($sku);
                    $newItem->setSubInvoice($orderInvoice);
                    $newItem->setSkuQuantity($products[$i]['skuQuantity']);
                    $em = $this->getDoctrine()->getManager();
                    $em->persist($newItem);
                    $em->flush();
                }
            }
            $this->checkSkuReserve(
                $data['subInvoice'],
                $repOrderItem,
                $data['orderDate'],
                $selectWarehouseCode['selectWarehouse'],
                $repMaterialQtyReserve
            );

            $materialReserves = $repInvoiceReserveMaterial->findMaterialNameReserve($data['subInvoice']);
            $maxQty = 0;

            $materialReserveObj = $repOrderInvoice->findOneBy(array('subInvoice' => $data['subInvoice']));
            $strMaterial = $materialReserveObj->getMaterialLabel();
            $strMaterialCodeMax = $materialReserveObj->getPrimaryMaterialCode();
            $strMaterialNameMax = $materialReserveObj->getPrimaryMaterialName();

            foreach ($materialReserves as $materialReserve) {
                if ($materialReserve["qty"] >= $maxQty) {
                    $maxQty = $materialReserve["qty"];
                    $strMaterialCodeMax = $materialReserve["materialCode"];
                    $strMaterialNameMax = $materialReserve["materialName"];
                }
                $strMaterial .= $materialReserve["materialName"] . '(' . $materialReserve["qty"] . '),';
            }

            $materialReserveObj->setPrimaryMaterialQuantity($maxQty);
            $materialReserveObj->setMaterialLabel($strMaterial);
            $materialReserveObj->setPrimaryMaterialCode($strMaterialCodeMax);
            $materialReserveObj->setPrimaryMaterialName($strMaterialNameMax);
            $em = $this->getDoctrine()->getManager();
            $em->flush();
            $output = 'SUCCESS';
        }
        return $this->json(array('output' => $output));
    }

    public function checkSkuReserve(
        string $subInvoice,
        $repOrderItem,
        string $date,
        string $warehouseCode,
        $repMaterialQtyReserve
    )
    {
        date_default_timezone_set("Asia/Bangkok");
        $output = [];
        $summaryMaterial = [];
        $qtys = $repOrderItem->findBomQty($subInvoice);

        foreach ($qtys as $qty) {
            $material = $qty["materialCode"];
            $materialBomQty = $qty["bomQty"];
            $skuQuantity = $qty["skuQuantity"];

            if (!array_key_exists($material, $summaryMaterial)) {
                $summaryMaterial[$material] = 0;
                $summaryMaterial[$material] += ($materialBomQty * $skuQuantity);
            } else {
                $summaryMaterial[$material] += ($materialBomQty * $skuQuantity);
            }
        }

        foreach ($summaryMaterial as $materialCode => $quantity) {
            $invoiceReserve = new InvoiceReserveMaterial();
            $invoiceReserve->setInvoice($subInvoice);
            $invoiceReserve->setInvoiceDate(new \DateTime($date, new \DateTimeZone('Asia/Bangkok')));
            $invoiceReserve->setWarehouseCode($warehouseCode);
            $invoiceReserve->setMaterialCode($materialCode);
            $invoiceReserve->setMaterialQty($quantity);
            $invoiceReserve->setTransactionType("RES");

            $em = $this->getDoctrine()->getManager();
            $em->persist($invoiceReserve);
            $em->flush();

            //Save Quantity to Material Qty Reserve
            $materialQtyByWarehouse = $repMaterialQtyReserve->findReserveQtyByWarehouse($warehouseCode, $materialCode);

            if ($materialQtyByWarehouse == null) {
                $materialQtyReserve = new MaterialQtyReserve();
                $materialQtyReserve->setMaterialCode($materialCode);
                $materialQtyReserve->setQuantity($quantity);
                $materialQtyReserve->setWarehouseCode($warehouseCode);

                $em->persist($materialQtyReserve);
                $em->flush();
            } else {
                $materialQtyByWarehouse = $materialQtyByWarehouse[0];
                $totalReserve = $quantity + $materialQtyByWarehouse["quantity"];

                $reservedQtyObj = $repMaterialQtyReserve->findOneBy(
                    array('warehouseCode' => $warehouseCode,
                        'materialCode' => $materialCode
                    )
                );
                $reservedQtyObj->setQuantity($totalReserve);
                $em->flush();
            }


        }
        return $output;
    }

    public function checkWarehouseAvailable(
        string $postCode,
        string $warehouseCode,
        $repWarehouseConfig,
        $productList,
        $materialQtyRepository,
        $materialQtyReserveRepository,
        $bomRepository,
        $repSkuInfo,
        string $logistic
    )
    {
        $warehouseAvailable = $repWarehouseConfig->findBy(array('zipcode' => $postCode));
        $listSku = [];
        if ($warehouseAvailable == null) {
            $selectWarehouseCode = $warehouseCode;
            $selectLogistic = $logistic;
        } else {
            $meet_require = true;
            foreach ($productList as $product) {
                //list material require
                $listSku = $this->availableSku(
                    $materialQtyRepository,
                    $materialQtyReserveRepository,
                    $bomRepository,
                    $repSkuInfo,
                    $product['skuCode'],
                    $warehouseAvailable[0]->getWarehouseCode()
                );

                if ($product['skuQuantity'] > $listSku['available']) {
                    $meet_require = false;
                }
            }
            if ($meet_require) {
                $selectWarehouseCode = $warehouseAvailable[0]->getWarehouseCode();
                $selectLogistic = "HOL";
            } else {
                $selectWarehouseCode = $warehouseCode;
                $selectLogistic = $logistic;
            }

        }
        if ($selectLogistic != "HOL") {
            if ($selectLogistic == "ALPHA_CANSEND") {
                $courierSelect = "ALPHA";
            } elseif ($selectLogistic == "KERRY_EXPRESS") {
                $courierSelect = "KERRY";
            } elseif ($selectLogistic == "NIKO_LOGISTICS") {
                $courierSelect = "NIKOS";
            } elseif ($selectLogistic == "THAI_POST") {
                $courierSelect = "THPOST";
            } elseif ($selectLogistic == "DHL_EXPRESS") {
                $courierSelect = "DHL";
            } elseif ($selectLogistic == "ALPHA_EXPRESS") {
                $courierSelect = "ALPHA";
            } else {
                $courierSelect = "ERR";
            }
        } else {
            $courierSelect = "HOL";
        }

        return array('selectWarehouse' => $selectWarehouseCode,
            'selectLogistic' => $courierSelect);
    }

    public function availableSku(
        $materialQtyRepository,
        $materialQtyReserveRepository,
        $bomRepository,
        $repSkuInfo,
        $skuCode,
        $warehouseCode
    )
    {
        $output = array();
        $ratioQty = [];
        $ratioQtyReserve = [];

        if ($skuCode == null && $warehouseCode == null) {
            return false;
        } else {
            $skuObj = $repSkuInfo->findOneBy(array('skuCode' => $skuCode));

            /****************************Calculate Available SKU****************************/
            /* 1.หาของใน BOM */
            $bomObj = $bomRepository->findBomQtyBySku($skuObj);
            for ($i = 0; $i < count($bomObj); $i++) {
                $materialCodeArray[$i] = $bomObj[$i]->getMaterial()->getMaterialCode();
                $bomQtyArray[$i] = $bomObj[$i]->getQuantity();
            }
            /* 2.เช็คว่า แต่ละMaterial มีเท่าไหร่ ใน Summary */
            $materialQtyObj = $materialQtyRepository->sumQtyByWarehouse($warehouseCode, $materialCodeArray);

            // $materialQtyObj =$materialTransactionRepository->sumQtyByWarehouse($warehouseCode, $materialCodeArray);
            if ($materialQtyObj == null) {
                $available=0;
            } else {
                /* 3.หา Material ที่มีจำนวนน้อยที่สุด */
                for ($q = 0; $q < count($materialQtyObj); $q++) {
                    /* 4. qty(sum) / qty(m.bom) return available(int) */
                    $ratioQty[$q] = $materialQtyObj[$q]["materialQty"] / $bomQtyArray[$q];
                }
                $available = floor(min($ratioQty));
            }

            $materialQtyReserve = $materialQtyReserveRepository->findReserveQtyInWarehouse($warehouseCode, $materialCodeArray);
            if($materialQtyReserve==null){
                $qtyReserve=0;
//                $materialQtyReserveC = new $materialQtyReserveRepository;
            } else {
                for ($r = 0; $r < count($materialQtyReserve); $r++) {
                    $ratioQtyReserve[$r] = $materialQtyReserve[$r]["quantity"] / $bomQtyArray[$r];
                }
                $qtyReserve = floor(min($ratioQtyReserve));
            }
            /*******************************************************************************/
            $output = array(
                "skuCode" => $skuObj->getSkuCode(),
                "skuName" => $skuObj->getSkuName(),
                "available" => $available - $qtyReserve
            );
            return $output;
        }
    }

    /**
     * @Route("/pending/info", name="pending_info")
     */
    public function pendingInfo(
        Request $request,
        PaginatorInterface $paginator,
        OrderInvoiceRepository $repOrderInvoice
    )
    {
        date_default_timezone_set("Asia/Bangkok");
        $strSearch = $request->query->get('search');
        $warehouseCode = $this->getUser()->getDefaultWarehouse()->getWarehouseCode();
        $listPending = $repOrderInvoice->findInvoicePending($warehouseCode);

        $form = $this->createForm(UpdatePendingType::class);
        $form->handleRequest($request);

        $result = $paginator->paginate(
            $listPending,
            $request->query->getInt('page', 1),
            $request->query->getInt('limit', 10)
        );

        if ($strSearch != "" && mb_strlen($strSearch) >= 3) {
            $pendingResult = $repOrderInvoice->searchPending($request->query->get('search'));
            $result = $paginator->paginate(
                $pendingResult,
                $request->query->getInt('page', 1)/*page number*/,
                10/*limit per page*/
            );
        }
        $entityManager = $this->getDoctrine()->getManager();
        if ($form->isSubmitted()) {

            $itemObj = $form->get('hiddenForm')->getData();
            $itemJson = json_decode($itemObj, true);

            if ($itemJson != null) {
                foreach ($itemJson as $subInvoice) {
                    $orderInvoiceInfo = $repOrderInvoice->find($subInvoice["value"]);

                    $orderInvoiceInfo->setInvoiceStatus(0);
                }
                $entityManager->flush();
                $this->addFlash('success', 'แก้ไขข้อมูลสำเร็จแล้ว');
                return $this->redirect($this->generateUrl('pending_info'));

            } else {
                $this->addFlash('error', 'กรุณาเลือกรายการให้ถูกต้อง');
            }
        }
        return $this->render('pending/index.html.twig', array(
            'formPending' => $form->createView(),
            'items' => $result,
        ));
    }

    /**
     * @Route("/api/cancel/post", methods={"POST"})
     */
    public function apiCancelPost(Request $request,
                                  OrderInvoiceRepository $repOrderInvoice,
                                  OrderInvoiceItemRepository $repOrderItem,
                                  MaterialQtyReserveRepository $repMaterialQtyReserve,
                                  InvoiceReserveMaterialRepository $repInvoiceReserveMaterial
    )
    {
        $em = $this->getDoctrine()->getManager();
        $summaryMaterial = [];
        $data = json_decode($request->getContent(), true);
        $orderInvoiceInfo = $repOrderInvoice->findOneBy(array('subInvoice' => $data['subInvoice'],'warehouseCode'=>$data['warehouseCode']));
        if ($orderInvoiceInfo == null) {
            $output = 'ERROR_DATA_NOT_FOUND';
        } elseif ($orderInvoiceInfo->getInvoiceStatus() == 1) {
            $output = 'ERROR_SUB_INVOICE_SENT';
        } else {
            $orderInvoiceInfo->setInvoiceStatus(3);

            $qtys = $repOrderItem->findBomQty($data['subInvoice']);
            foreach ($qtys as $qty) {
                $material = $qty["materialCode"];
                $materialBomQty = $qty["bomQty"];
                $skuQuantity = $qty["skuQuantity"];

                if (!array_key_exists($material, $summaryMaterial)) {
                    $summaryMaterial[$material] = 0;
                    $summaryMaterial[$material] += ($materialBomQty * $skuQuantity);
                } else {
                    $summaryMaterial[$material] += ($materialBomQty * $skuQuantity);
                }
            }

            foreach ($summaryMaterial as $materialCode => $quantity) {
                $invoiceReserve = new InvoiceReserveMaterial();
                $invoiceReserve->setInvoice($data['subInvoice']);
                $invoiceReserve->setInvoiceDate(new \DateTime("now", new \DateTimeZone('Asia/Bangkok')));//Datetime which cancel
                $invoiceReserve->setWarehouseCode($data['warehouseCode']);
                $invoiceReserve->setMaterialCode($materialCode);
                $invoiceReserve->setMaterialQty($quantity);
                $invoiceReserve->setTransactionType("CAN");

                $em = $this->getDoctrine()->getManager();
                $em->persist($invoiceReserve);
                $em->flush();

                $materialQtyByWarehouse = $repMaterialQtyReserve->findReserveQtyByWarehouse($data['warehouseCode'], $materialCode);
//                $materialQtyByWarehouse = $materialQtyByWarehouse[0];
                $totalReserve = $materialQtyByWarehouse[0]["quantity"] - $quantity;
                $reservedQtyObj = $repMaterialQtyReserve->findOneBy(
                    array('warehouseCode' => $data['warehouseCode'],
                        'materialCode' => $materialCode
                    )
                );
                $reservedQtyObj->setQuantity($totalReserve);
            }

            $em->flush();
            $output = 'SUB_INVOICE_'.$data['subInvoice'].'_CANCELED';
        }
        return $this->json(array('status' => $output));
    }
}
