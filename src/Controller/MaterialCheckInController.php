<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\File\UploadedFile;

use Knp\Component\Pager\PaginatorInterface;

use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;

// import the Intervention Image Manager Class
use Intervention\Image\ImageManager;

use App\Entity\CheckIn;
use App\Entity\CheckInItem;
use App\Entity\MaterialInfo;
use App\Entity\MaterialTransaction;
use App\Entity\MaterialQty;
use App\Entity\WarehouseInfo;
use App\Form\MaterialCheckInType;

use App\Repository\CheckInItemRepository;
use App\Repository\CheckInRepository;
use App\Repository\MaterialInfoRepository;
use App\Repository\MaterialQtyRepository;

use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class MaterialCheckInController extends AbstractController
{
    /**
     * @Route("/checkin/info", name="checkin_info")
     */
    public function checkInInfo(Request $request,
                                CheckInRepository $checkInRepository,
                                PaginatorInterface $paginator,
                                AuthorizationCheckerInterface $authChecker
    ) {
        date_default_timezone_set("Asia/Bangkok");
        $warehouseCode = $this->getUser()->getDefaultWarehouse()->getWarehouseCode();
        $strSearch = $request->query->get('search');
        if(true === $authChecker->isGranted('ROLE_ADMIN')) {
            $listCheckIn = $checkInRepository->findListCheckInAdmin();
        } else {
            $listCheckIn = $checkInRepository->findListCheckInUser($warehouseCode);
        }
        $result = $paginator->paginate(
            $listCheckIn,
            $request->query->getInt('page', 1)/*page number*/,
            10/*limit per page*/
        );
        if ($strSearch != "" && mb_strlen($strSearch) >= 3) {
            $checkInResult = $checkInRepository->findAllCheckInQuery($request->query->get('search'));
            $result = $paginator->paginate(
                $checkInResult,
                $request->query->getInt('page', 1)/*page number*/,
                10/*limit per page*/
            );
        }
        return $this->render('material_check_in/index.html.twig', array(
            'items' => $result
        ));
    }

    /**
     * @Route("/checkin/add", name="checkin_add")
     */
    public function addCheckIn(
        Request $request,
        CheckInRepository $checkInRepository,
        MaterialInfoRepository $materialInfoRepository,
        MaterialQtyRepository $materialQtyRepository,
        UserRepository $userRepository
    ) {
        date_default_timezone_set("Asia/Bangkok");
        $username = $this->getUser()->getUsername();
        $warehouseCode = $this->getUser()->getDefaultWarehouse()->getWarehouseCode();
        $listUser = $userRepository->findBy(array('username' => $username));

        $listCheckIn = $checkInRepository->findCheckInByWarehouseCode($warehouseCode);
        $listCheckInNumber = $listCheckIn[0]["countCheckIn"] + 1;
        $date = date("ymd");
        $orderCode = str_pad($listCheckInNumber, 4, "0", STR_PAD_LEFT);
        $orderCodeString = "CIN" . $date . $orderCode;

        $form = $this->createForm(MaterialCheckInType::class, $listUser);
        $form->get('warehouseCode')->getData($listUser);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $itemObj = $form->get('hiddenForm')->getData();
            $checkInCode = $form->get('checkInCode')->getData();
            if ($itemObj == null) {
                $this->addFlash('error', 'กรุณาเพิ่ม Material');
                return $this->redirect($this->generateUrl('checkin_add'));
            } elseif ($checkInRepository->findOneBy(array('checkInCode' => $checkInCode))) {
                $this->addFlash('error', 'รหัสการ Check In ซ้ำ');
                return $this->redirect($this->generateUrl('checkin_add'));
            } else {
//                $encoders = [new XmlEncoder(), new JsonEncoder()];
//                $serializer = new Serializer(array(new GetSetMethodNormalizer()), array('json' => new JsonEncoder()));
//
//                $itemJson = $serializer->decode($itemObj, 'json');
                $itemJson = json_decode($itemObj, true);

                $checkIn = new CheckIn();
                $checkIn->setCheckInCode($form->get('checkInCode')->getData());
                $checkIn->setCheckInRefNo($form->get('checkInRefNo')->getData());
                $checkIn->setCheckInDate(new \DateTime("now", new \DateTimeZone('Asia/Bangkok')));
                $checkIn->setCheckInNote($form->get('checkInNote')->getData());
                $checkIn->setWarehouseCode($form->get('warehouseCode')->getData());

                //upload Image//////////////////////////
                if ($form->get('checkInImage')->getData() != null) {
                    $file = $form->get('checkInImage')->getData();
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
                    $checkIn->setCheckInImage($filename);
                }

                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($checkIn);
                $entityManager->flush();
                for ($i = 0; $i < count($itemJson); $i++) {
                    $checkInCodeObj = $checkInRepository->findOneBy(array(
                        'checkInCode' => $form->get('checkInCode')->getData()
                    ));
                    //Insert Check In Item Data
                    $checkInItem = new CheckInItem();
                    $checkInItem->setCheckInCode($form->get('checkInCode')->getData());
                    $materialObj = $materialInfoRepository->findOneBy(array(
                        'materialCode' => $itemJson[$i]["materialCode"]
                    ));
                    $checkInItem->setMaterial($materialObj);
                    $checkInItem->setQuantity($itemJson[$i]["materialQty"]);
                    //Update ProductQTY
                    $materialQtyObj = $materialQtyRepository->findOneBy(array(
                        'materialCode' => $itemJson[$i]["materialCode"],
                        'warehouseCode' => $warehouseCode
                    ));
                    if ($materialQtyObj == null) {
                        $materialQtyObj = new MaterialQty();
                        $materialQtyObj->setMaterialCode($materialObj);
                        $materialQtyObj->setMaterialQty(0);
                        $materialQtyObj->setWarehouseCode($form->get('warehouseCode')->getData());
                        $entityManager->persist($materialQtyObj);
                        $entityManager->flush();
                        $materialTotal = $materialQtyObj->getMaterialQty();
                        $warehouseCode = $materialQtyObj->getWarehouseCode();
                        $checkMaterialQty = $materialTotal + $itemJson[$i]["materialQty"];

                    } else {
                        $materialTotal = $materialQtyObj->getMaterialQty();
                        $warehouseCode = $materialQtyObj->getWarehouseCode();
                        $checkMaterialQty = $materialTotal + $itemJson[$i]["materialQty"];
                    }

                    if ($warehouseCode == null) {
                        $materialQtyObj->setMaterialQty($checkMaterialQty);
                        $materialQtyObj->setWarehouseCode($form->get('warehouseCode')->getData());
                    } else {
                        $materialQtyObj->setMaterialQty($checkMaterialQty);
                    }

                    //Insert Material Transaction Data
                    $materialTrans = new MaterialTransaction();
                    $materialTrans->setMaterialTransactionCode($form->get('checkInCode')->getData());
                    $materialTrans->setMaterialTransactionDate(new \DateTime("now", new \DateTimeZone('Asia/Bangkok')));
                    $materialTrans->setMaterialCode($itemJson[$i]["materialCode"]);
                    $materialTrans->setQuantity($itemJson[$i]["materialQty"]);
                    $materialTrans->setMaterialName($itemJson[$i]["materialName"]);
                    $materialTrans->setWarehouseCode($form->get('warehouseCode')->getData());
                    $materialTrans->setTransactionType("CIN");
                    $materialTrans->setReferenceId($form->get('checkInRefNo')->getData());

                    $entityManager = $this->getDoctrine()->getManager();
                    $entityManager->persist($materialTrans);
                    $entityManager->persist($checkInItem);
                    $entityManager->flush();
                }
                $this->addFlash('success', 'บันทึกข้อมูล ' . $form->get('checkInCode')->getData() . ' สำเร็จแล้ว');
            }

            return $this->redirect($this->generateUrl('checkin_info'));
        }

        return $this->render('material_check_in/add.html.twig', array(
            'formCheckIn' => $form->createView(),
            'orderCode' => $orderCodeString
        ));
    }

    /***************************************************API PART***************************************************/
    /**
     * @Route("/checkin/checkmaterial", name="checkin_checkmaterial")
     */
    public function checkMaterialCode(Request $request, MaterialInfoRepository $materialInfoRepository)
    {
        $materialCode = $request->request->get('materialCode');
        $materialObj = $materialInfoRepository->findOneBy(array(
            'materialCode' => $materialCode
        ));
        $output = array('MaterialName' => $materialObj->getMaterialName());

        return $this->json($output);
    }

    /**
     * @Route("/checkin/view", name="view_checkin")
     */
    public function viewCheckIn(Request $request, CheckInItemRepository $checkInItemRepository)
    {
        $checkInCode = $request->request->get('checkInCode');
        $checkInItemObj = $checkInItemRepository->findByCheckInCode($checkInCode);
        $output = array(
            "checkInCode" => $checkInCode,
            "checkInItem" => $checkInItemObj
        );
        return $this->json($output);
    }
}
