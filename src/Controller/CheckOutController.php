<?php

namespace App\Controller;

use App\Entity\CheckOut;
use App\Entity\CheckOutItem;
use App\Repository\CheckOutRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

// use App\Controller\datetime;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

// import the Intervention Image Manager Class
use Intervention\Image\ImageManager;

use App\Entity\Packing;
use App\Entity\PackingBatchInvoice;
use App\Entity\MaterialTransaction;
use App\Entity\OrderInvoiceItem;
use App\Entity\InvoiceReserveMaterial;
use App\Entity\MaterialQtyReserve;
use App\Entity\MaterialQty;

use App\Form\MaterialCheckOutType;

use App\Repository\PackingRepository;
use App\Repository\MaterialQtyRepository;
use App\Repository\MaterialQtyReserveRepository;
use App\Repository\MaterialInfoRepository;
use App\Repository\UserRepository;
use App\Repository\CheckOutItemRepository;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class CheckOutController extends AbstractController
{
    /**
     * @Route("/checkout/info", name="checkout_info")
     */
    public function checkOutInfo(Request $request,
                                CheckOutRepository $checkOutRepository,
                                PaginatorInterface $paginator,
                                AuthorizationCheckerInterface $authChecker
    ) {
        date_default_timezone_set("Asia/Bangkok");
        $warehouseCode = $this->getUser()->getDefaultWarehouse()->getWarehouseCode();
        $strSearch = $request->query->get('search');
        if(true === $authChecker->isGranted('ROLE_ADMIN')) {
            $listCheckOut = $checkOutRepository->findListCheckOutAdmin();
        } else {
            $listCheckOut = $checkOutRepository->findListCheckOutUser($warehouseCode);
        }
        $result = $paginator->paginate(
            $listCheckOut,
            $request->query->getInt('page', 1)/*page number*/,
            10/*limit per page*/
        );
        if ($strSearch != "" && mb_strlen($strSearch) >= 3) {
            $checkOutResult = $checkOutRepository->findAllCheckOutQuery($request->query->get('search'));
            $result = $paginator->paginate(
                $checkOutResult,
                $request->query->getInt('page', 1)/*page number*/,
                10/*limit per page*/
            );
        }
        return $this->render('check_out/index.html.twig', array(
            'items' => $result
        ));
    }
    /**
     * @Route("/confirm/check/out", methods={"POST"})
     */
    public function confirmCheckOut(
        Request $request,
        PackingRepository $repPacking,
        MaterialQtyRepository $repMaterialQty,
        MaterialQtyReserveRepository $repMaterialQtyReserve
    )
    {
        $packingBatchId = intval($request->request->get('packingBatchId'));

        if ($packingBatchId === null) {
            return $this->json(false);
        } else {
            $em = $this->getDoctrine()->getManager();
            /*Insert Material Transaction */
            $packingSumEachMaterialObjs = $repPacking->findSumMaterialByPackingId($packingBatchId);
            foreach ($packingSumEachMaterialObjs as $packingSumEachMaterialObj) {
                $date = date("ymd");
                $orderCode = str_pad($packingSumEachMaterialObj["packingBatchId"], 4, "0", STR_PAD_LEFT);
                $orderCodeString = "COT" . $date . $orderCode;

                $materialTrans = new MaterialTransaction();
                $materialTrans->setMaterialTransactionCode($orderCodeString);
                $materialTrans->setMaterialTransactionDate($packingSumEachMaterialObj["startPackingBatchAt"]);
                $materialTrans->setMaterialCode($packingSumEachMaterialObj["materialCode"]);
                $materialTrans->setQuantity($packingSumEachMaterialObj["mQty"]);
                $materialTrans->setMaterialName($packingSumEachMaterialObj["materialName"]);
                $materialTrans->setWarehouseCode($packingSumEachMaterialObj["warehouseCode"]);
                $materialTrans->setTransactionType("COT");
                $materialTrans->setReferenceId($packingSumEachMaterialObj["packingBatchId"]);

                $invoiceReserve = new InvoiceReserveMaterial();
                $invoiceReserve->setInvoice($packingSumEachMaterialObj["packingBatchId"]);
                $invoiceReserve->setInvoiceDate($packingSumEachMaterialObj["startPackingBatchAt"]);
                $invoiceReserve->setWarehouseCode($packingSumEachMaterialObj["warehouseCode"]);
                $invoiceReserve->setMaterialCode($packingSumEachMaterialObj["materialCode"]);
                $invoiceReserve->setMaterialQty(($packingSumEachMaterialObj["mQty"]) * -1);
                $invoiceReserve->setTransactionType("COT");

                $em->persist($materialTrans);
                $em->persist($invoiceReserve);
                $em->flush();

                $this->updateMaterialQty(
                    $packingSumEachMaterialObj["warehouseCode"],
                    $packingSumEachMaterialObj["materialCode"],
                    $packingSumEachMaterialObj["mQty"],
                    $repMaterialQty,
                    $repMaterialQtyReserve
                );
            }
            $em->flush();
            return $this->json(true);
        }
    }

    /**
     * @Route("/checkout/add", name="checkout_add")
     */
    public function addCheckOut(
        Request $request,
        MaterialInfoRepository $materialInfoRepository,
        MaterialQtyRepository $materialQtyRepository,
        UserRepository $userRepository,
        CheckOutRepository $checkOutRepository
    )
    {
        date_default_timezone_set("Asia/Bangkok");
        $username = $this->getUser()->getUsername();
        $warehouseCode = $this->getUser()->getDefaultWarehouse()->getWarehouseCode();
        $listUser = $userRepository->findBy(array('username' => $username));
        $countCheckOut = $checkOutRepository->count(array("warehouseCode" => $warehouseCode));
        $countCheckOutNumber = $countCheckOut + 1;
        $date = date("ymd");
        $orderCode = str_pad($countCheckOutNumber, 4, "0", STR_PAD_LEFT);
        $orderCodeString = "CON" . $date . $orderCode;

        $form = $this->createForm(MaterialCheckOutType::class, $listUser);
        $form->get('warehouseCode')->setData($this->getUser()->getDefaultWarehouse()->getWarehouseName());

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $itemObj = $form->get('hiddenForm')->getData();
            $checkOutCode = $form->get('checkOutCode')->getData();
            $checkOutNode = "";
            if ($form->get('checkOutType')->getData() == 1) {
                $checkOutNode = "สินค้าไม่ผ่าน QC";
            } elseif ($form->get('checkOutType')->getData() == 2) {
                $checkOutNode = "เบิกเพื่อทดสอบ";
            } elseif ($form->get('checkOutType')->getData() == 3) {
                $checkOutNode = $request->request->get("otherText");
            }
            if ($itemObj == null) {
                $this->addFlash('error', 'กรุณาเพิ่ม Material');
                return $this->redirect($this->generateUrl('checkout_add'));
            } else {
                $checkOutDataObj = json_decode($itemObj, true);
                foreach ($checkOutDataObj as $checkOutData) {
                    //Update ProductQTY
                    $materialQtyObj = $materialQtyRepository->findOneBy(array(
                        'materialCode' => $checkOutData["materialCode"],
                        'warehouseCode' => $warehouseCode
                    ));
                    $materialTotal = $materialQtyObj->getMaterialQty();
                    $warehouseCodeObj = $materialQtyObj->getWarehouseCode();

                    if ($checkOutData["materialQty"] > $materialTotal) {
                        // Material not enough
                        $this->addFlash("errorMaterial", "มีสินค้า " . $checkOutData["materialName"] . "ไม่พอ มีจำนวนทั้งหมด " . $materialTotal);
                        return $this->redirect($this->generateUrl('checkout_add'));
                    } else {
                        // Material enough
                        $checkOutItem = new CheckOutItem();
                        $checkOutItem->setCheckOutCode($form->get('checkOutCode')->getData());
                        $materialObj = $materialInfoRepository->findOneBy(array(
                            'materialCode' => $checkOutData["materialCode"]
                        ));
                        $checkOutItem->setMaterial($materialObj);
                        $checkOutItem->setQuantity($checkOutData["materialQty"]);
                        $entityManager->persist($checkOutItem);

                        // Insert Transaction
                        $materialTrans = new MaterialTransaction();
                        $materialTrans->setMaterialTransactionCode($checkOutCode);
                        $materialTrans->setMaterialTransactionDate(new \DateTime("now", new \DateTimeZone('Asia/Bangkok')));
                        $materialTrans->setMaterialCode($checkOutData["materialCode"]);
                        $materialTrans->setQuantity($checkOutData["materialQty"]);
                        $materialTrans->setMaterialName($checkOutData["materialName"]);
                        $materialTrans->setWarehouseCode($warehouseCodeObj);
                        $materialTrans->setTransactionType("CON");
                        $materialTrans->setReferenceId($checkOutCode);
                        $entityManager->persist($materialTrans);


                        $checkMaterialQty = $materialTotal - $checkOutData["materialQty"];
                        $materialQtyObj->setMaterialQty($checkMaterialQty);
                        $materialQtyObj->setWarehouseCode($warehouseCode);

                    }
                }
                $entityManager->flush();

                $checkOut = new CheckOut();
                $checkOut->setCheckOutCode($form->get('checkOutCode')->getData());
                $checkOut->setCheckOutRefNo($form->get('checkOutRefNo')->getData());
                $checkOut->setCheckOutDate(new \DateTime("now", new \DateTimeZone('Asia/Bangkok')));
                $checkOut->setCheckOutNote($checkOutNode);
                $checkOut->setWarehouseCode($warehouseCode);
                //upload Image//////////////////////////
                if ($form->get('checkOutImage')->getData() != null) {
                    $file = $form->get('checkOutImage')->getData();
                    $images_l_directory = $this->getParameter('images_l_directory');
                    $images_s_directory = $this->getParameter('images_s_directory');
                    $filename = md5(uniqid()) . "." . $file->getClientOriginalExtension();
                    $file->move(
                        $images_l_directory,
                        $filename
                    );

                    // configure with favored image driver (gd by default)
                    $manager = new ImageManager(array('driver' => 'gd'));
                    $image_l = $manager->make($images_l_directory . '/' . $filename);
                    $image_s = $image_l;
                    if ($image_l->width() > $image_l->height()) {
                        $image_l->widen(1024);
                        $image_l->save($images_l_directory . '/' . $filename);
                        $image_s->fit(50);
                    } elseif ($image_l->width() == $image_l->height()) {
                        $image_l->fit(1024);
                        $image_l->save($images_l_directory . '/' . $filename);
                        $image_l->fit(50);
                    } else {
                        $image_l->heighten(1024);
                        $image_l->save($images_l_directory . '/' . $filename);
                        $image_s->fit(50);
                    }
                    $image_s->save($images_s_directory . '/' . $filename);
                    $checkOut->setCheckOutImage($filename);
                }

                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($checkOut);
                $entityManager->flush();
                $this->addFlash('success', 'บันทึกข้อมูล ' . $form->get('checkOutCode')->getData() . ' สำเร็จแล้ว');
            }

            return $this->redirect($this->generateUrl('checkout_info'));
        }

        return $this->render('check_out/add.html.twig', array(
            'formCheckOut' => $form->createView(),
            'orderCode' => $orderCodeString
        ));
    }

    public function updateMaterialQty(
        $warehouseCode,
        $materialCode,
        $packingMaterialQty,
        $repMaterialQty,
        $repMaterialQtyReserve
    ) {
        $reservedQtyObj = $repMaterialQtyReserve->findOneBy(
            array('warehouseCode' => $warehouseCode, 'materialCode' => $materialCode)
        );

        $materialQtyObj = $repMaterialQty->findOneBy(
            array('warehouseCode' => $warehouseCode, 'materialCode' => $materialCode)
        );

        $reservedQtyObj->setQuantity($reservedQtyObj->getQuantity() - $packingMaterialQty);
        $materialQtyObj->setMaterialQty($materialQtyObj->getMaterialQty() - $packingMaterialQty);
    }

    /**
     * @Route("/checkout/checkMaterial", name="checkout_checkmaterial")
     */
    public function checkMaterialQty(Request $request, MaterialQtyRepository $materialQtyRepository)
    {
        $materialCode = $request->request->get('materialCode');
        $warehouseCode = $this->getUser()->getDefaultWarehouse()->getWarehouseCode();
        $materialObj = $materialQtyRepository->findOneBy(array(
            'materialCode' => $materialCode,
            'warehouseCode' => $warehouseCode
        ));

        return $this->json($materialObj);
    }

    /**
     * @Route("/checkout/view", name="view_checkout")
     */
    public function viewCheckout(Request $request, CheckOutItemRepository $checkOutItemRepository)
    {
        $checkOutCode = $request->request->get('checkOutCode');
        $checkOutItemObj = $checkOutItemRepository->findByCheckOutCode($checkOutCode);
        $output = array(
            "checkOutCode" => $checkOutCode,
            "checkOutItem" => $checkOutItemObj
        );
        return $this->json($output);
    }

}
