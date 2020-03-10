<?php

namespace App\Controller;

use App\Repository\BomRepository;
use App\Repository\MaterialInfoRepository;
use App\Service\Api945HoldingService;
use App\Service\SkuUtilService;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

// import the Intervention Image Manager Class
use Intervention\Image\ImageManager;

use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;


use App\Form\SkuInfoType;
use App\Form\ViewSkuType;
use App\Form\UpdateSKUType;

use App\Entity\MaterialInfo;
use App\Entity\SkuInfo;
use App\Entity\Bom;
use App\Repository\SkuInfoRepository;

class SkuInfoController extends AbstractController
{
    /**
     * @Route("/sku/info", name="sku_info")
     */
    public function viewSkuInfo(Request $request, SkuInfoRepository $skuInfoRepository, PaginatorInterface $paginator)
    {
        date_default_timezone_set("Asia/Bangkok");
        $strSearch=$request->query->get('search');
        $listSku = $this->getDoctrine()->getRepository(SkuInfo::class)->findAll();
        $result = $paginator->paginate(
            $listSku,
            $request->query->getInt('page', 1)/*page number*/,
            10/*limit per page*/
        );
        if($strSearch != "" && mb_strlen($strSearch) >= 3) {
            $skuResult = $skuInfoRepository->findAllSkuQuery($request->query->get('search'));
            $result = $paginator->paginate(
                $skuResult,
                $request->query->getInt('page', 1)/*page number*/,
                10/*limit per page*/
            );
        }
        return $this->render('sku_info/index.html.twig', array(
            'items' => $result
        ));
    }

    /**
     * @Route("/sku/add", name="sku_add")
     */
    public function addSkuInfo(Request $request)
    {
        $form = $this->createForm(SkuInfoType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $bomObj = $form->get('hiddenForm')->getData();
            $listSku = $this->getDoctrine()->getRepository(SkuInfo::class)->findBy(array(
                'skuCode' => $form->get('skuCode')->getData()
            ));
            if (count($listSku) > 0) {
                $this->addFlash('error', 'รหัส SKU นี้มีอยู่ในระบบแล้ว');
            } else {
                if ($bomObj == null) {
                    $this->addFlash('error', 'กรุณาเพิ่มสินค้า');
                } else {
                    $bomJson=json_decode($bomObj, true);
                    //Insert Sku Data
                    $skuInfo = new SkuInfo();
                    $skuInfo->setSkuCode($form->get('skuCode')->getData());
                    $skuInfo->setSkuName($form->get('skuName')->getData());
                    $skuInfo->setSkuStatus(1);

                    $entityManager = $this->getDoctrine()->getManager();
                    $entityManager->persist($skuInfo);
                    $entityManager->flush();

                    for ($i = 0; $i < count($bomJson); $i++) {
                        $materialObj = $this->getDoctrine()->getRepository(MaterialInfo::class)->findOneBy(array(
                            'materialCode' => $bomJson[$i]["materialCode"]
                        ));
                        $skuObj = $this->getDoctrine()->getRepository(SkuInfo::class)->findOneBy(array(
                            'skuCode' => $form->get('skuCode')->getData()
                        ));
                        $bom = new Bom();
                        $bom->setSkuCode($skuObj);
                        $bom->setMaterial($materialObj);
                        $bom->setQuantity($bomJson[$i]["materialQty"]);

                        $entityManager = $this->getDoctrine()->getManager();
                        $entityManager->persist($bom);
                        $entityManager->flush();
                    }
                    $this->addFlash('success', 'บันทึกข้อมูลสำเร็จแล้ว');
                    return $this->redirect($this->generateUrl('sku_info'));
                }
            }
        }

        return $this->render('sku_info/add.html.twig', array(
            'formSku' => $form->createView(),
        ));
    }

    /**
     * @Route("/sku/update/{skuCode}", name="sku_update")
     */
    public function updateSkuInfo(Request $request, $skuCode)
    {
        // $data = $request->request->get('request');
        $skuInfoObj = $this->getDoctrine()->getRepository(SkuInfo::class)->findOneBy(array('skuCode' => $skuCode));

        $form = $this->createForm(UpdateSKUType::class, $skuInfoObj);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $bomObj = $form->get('hiddenForm')->getData();
            if ($bomObj == null) {
                $this->addFlash('error', 'กรุณาเพิ่มสินค้า');
                return $this->redirect($this->generateUrl('checkin_index'));
            } else {
                for ($i = 0; $i < intval($form->get('materialSizeForm')->getData()); $i++) {
                    $skuBomObj = $this->getDoctrine()->getRepository(Bom::class)->findBy(array('skuCode' => $skuCode));
                    $entityManager = $this->getDoctrine()->getManager();
                    foreach ($skuBomObj as $skuBom) {
                        $entityManager->remove($skuBom);
                    }
                    $entityManager->flush();
                }
                $skuBomObj = $this->getDoctrine()->getRepository(Bom::class)->findBy(array('skuCode' => $skuCode));
                if (count($skuBomObj) == 0) {
                    $encoders = [new XmlEncoder(), new JsonEncoder()];
                    $serializer = new Serializer(array(new GetSetMethodNormalizer()), array(
                        'json' => new JsonEncoder()
                    ));

                    $bomJson = $serializer->decode($bomObj, 'json');
                    for ($i = 0; $i < count($bomJson); $i++) {
                        $materialObj = $this->getDoctrine()->getRepository(MaterialInfo::class)->findOneBy(array(
                            'materialCode' => $bomJson[$i]["materialCode"]
                        ));
                        $skuObj = $this->getDoctrine()->getRepository(SkuInfo::class)->findOneBy(array(
                            'skuCode' => $skuCode
                        ));
                        $bom = new Bom();
                        $bom->setSkuCode($skuObj);
                        $bom->setMaterial($materialObj);
                        $bom->setQuantity($bomJson[$i]["materialQty"]);
                        $entityManager = $this->getDoctrine()->getManager();
                        $entityManager->persist($bom);
                        $entityManager->flush();
                    }
                    $this->addFlash('success', 'แก้ไขข้อมูลสำเร็จแล้ว');
                    return $this->redirect($this->generateUrl('sku_info'));
                }
            }
        }

        return $this->render('sku_info/update.html.twig', array(
            'formSku' => $form->createView(),
        ));
    }

    /***************************************************API PART***************************************************/

    // *
    //  * @Route("/sku/addsku", name="add_sku")
    //  * @param Request $request

    // public function addSkuInfo(Request $request)
    // {
    //     $skuCode = $request->request->get('skuCode');
    //     $skuName = $request->request->get('skuName');
    //     $output=array();
    //     //Insert Sku Data
    //     $skuInfo = new SkuInfo();
    //     $skuInfo->setSkuCode($skuCode);
    //     $skuInfo->setSkuName($skuName);
    //     $skuInfo->setSkuStatus(1);

    //     $entityManager = $this->getDoctrine()->getManager();
    //     $entityManager->persist($skuInfo);
    //     $entityManager->flush();
    //     $this->addFlash('success', 'บันทึกข้อมูลสำเร็จแล้ว');

    //     $output[]=array($skuCode);

    //     return new JsonResponse($output);
    // }

//    /**
//     * @Route("/sku/addBom", name="add_Bom")
//     * @param Request $request
//     */
    // public function addBom(Request $request)
    // {
    // $skuCode = $request->request->get('skuCode');
    // $materialCode = $request->request->get('materialCode');
    // $materialQty = intval($request->request->get('materialQty'));

    //Insert BOM Data
    // $bom = new Bom();
    // $bom->setSkuCode($skuCode);
    // $bom->setMaterial($materialObj);
    // $bom->setQuantity($materialQty);

    // $entityManager = $this->getDoctrine()->getManager();
    // $entityManager->persist($bom);
    // $entityManager->flush();

    //     return new JsonResponse($bom);
    // }

//    /**
//     * @Route("/sku/updateBom", name="update_Bom")
//     * @param Request $request
//     */
//    public function updateBOM(Request $request)
//    {
//        $output = array();
//        $skuCode = $request->request->get('skuCode');
//        $materialCode = $request->request->get('materialCode');
//        $materialName = $request->request->get('materialName');
//        $materialQty = intval($request->request->get('materialQty'));
//
//        $materialObj = $this->getDoctrine()->getRepository(MaterialInfo::class)->findOneBy(array(
//            'materialCode' => $materialCode
//        ));

        // if ($bomObj == null) {
//        $bom = new Bom();
//        $bom->setSkuCode($skuCode);
//        $bom->setMaterial($materialObj);
//        $bom->setQuantity($materialQty);
//        $entityManager = $this->getDoctrine()->getManager();
//        $entityManager->persist($bom);
//        $entityManager->flush();
//
//        $output = array('materialCode' => $bom->getMaterial()->getMaterialCode(), 'bomQty' => $bom->getQuantity());
        // } else {
        //Add New Bom By SKU
        // $bom = new Bom();
        // $bom->setSkuCode($skuCode);
        // $bom->setMaterialCode($materialCode);
        // $bom->setQuantity($materialQty);
        // $entityManager = $this->getDoctrine()->getManager();
        // $entityManager->persist($bom);
        // $entityManager->flush();

        // $bomObj->setQuantity($materialQty);
        // $entityManager = $this->getDoctrine()->getManager();
        // $entityManager->flush();

        // $output = array('materialCode1' => $bom->getMaterialCode(), 'bomQty1' => $bom->getQuantity());
        // }

//        return new JsonResponse($output);
//    }

//    /**
//     * @Route("/sku/deleteAllSkuItem", name="sku_deleteAllSkuItem")
//     * @param Request $request
//     */
//    public function deleteAllSkuItem(Request $request)
//    {
//        $output = array();
//        $skuCode = $request->request->get('skuCode');
//        $skuBomObj = $this->getDoctrine()->getRepository(Bom::class)->findBy(array('skuCode' => $skuCode));
//        $entityManager = $this->getDoctrine()->getManager();
//        foreach ($skuBomObj as $skuBom) {
//            $entityManager->remove($skuBom);
//        }
//
//        $entityManager->flush();
//
//        return $this->json(array());
//    }

    /**
     * @Route("/sku/updateStatus", name="sku_updateStatus")
     */
    public function updateSKUStatus(Request $request)
    {
        $output = array();
        $skuCode = $request->request->get('skuCode');
        $skuStatus = intval($request->request->get('skuStatus'));
        $skuInfoObj = $this->getDoctrine()->getRepository(SkuInfo::class)->findOneBy(array('skuCode' => $skuCode));
        $skuInfoObj->setSkuStatus($skuStatus);
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->flush();

        return new JsonResponse($skuCode);
    }

    /**
     * @Route("/sku/checkmaterial", name="sku_checkmaterial")
     */
    public function checkMaterialCode(Request $request)
    {
        $output = array();
        $materialCode = $request->request->get('materialCode');
        $materialObj = $this->getDoctrine()->getRepository(MaterialInfo::class)->findOneBy(array(
            'materialCode' => $materialCode
        ));

        $output = array('MaterialName' => $materialObj->getMaterialName());

        return new JsonResponse($output);
    }

    /**
     * @Route("/sku/view", name="view_sku")
     */
    public function viewSku(Request $request, BomRepository $bomRepository)
    {
        $skuCode = $request->request->get('skuCode');

        $bomObj = $bomRepository->findBySkuCode($skuCode);
        $output = array(
            "skuCode" => $skuCode,
            "bom" => $bomObj
        );
        return $this->json($output);
    }

    /**
     * @Route("/api/945holding/product/list")
     * @param Api945HoldingService $api
     * @return Response
     */
    public function api945HoldingProductList(Request $request, Api945HoldingService $api){
        $mer_id = 1;
        $start = intval($request->get("start","0"));

        return $this->json($api->listProduct($mer_id, $start));
    }

    /**
     * @Route("/api/sku/check")
     * @param SkuUtilService $skuUtil
     * @return Response
     */
    public function apiSkuCheck(Request $request, SkuUtilService $skuUtil) {
        $report = $skuUtil->checkSku();
        return $this->json($report);
    }
}
