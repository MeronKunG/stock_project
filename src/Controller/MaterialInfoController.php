<?php

namespace App\Controller;

use App\Repository\MaterialInfoRepository;
use App\Repository\MaterialQtyRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\File\UploadedFile;

use Knp\Component\Pager\PaginatorInterface;
// import the Intervention Image Manager Class
use Intervention\Image\ImageManager;

use App\Entity\MaterialInfo;
use App\Entity\MaterialQty;
use App\Entity\MaterialTransaction;

use App\Form\MaterialInfoType;
use App\Form\UpdateMaterialType;

class MaterialInfoController extends AbstractController
{
    /**
     * @Route("/material/info", name="material_info")
     */
    public function viewMaterialInfo(
        Request $request,
        PaginatorInterface $paginator,
        MaterialInfoRepository $materialInfoRepository
    ) {
        date_default_timezone_set("Asia/Bangkok");
        $strSearch=$request->query->get('search');
        $listMaterial = $this->getDoctrine()->getRepository(MaterialInfo::class)->findAll();
        $result = $paginator->paginate(
            $listMaterial,
            $request->query->getInt('page', 1)/*page number*/,
            10/*limit per page*/
        );
        if ($strSearch != "" && mb_strlen($strSearch)>= 3) {
            $materialResult = $materialInfoRepository->findByMaterialName($request->query->get('search'));
            $result = $paginator->paginate(
                $materialResult,
                $request->query->getInt('page', 1)/*page number*/,
                10/*limit per page*/
            );
        }
        return $this->render('material_info/index.html.twig', array(
            'materials' => $result
        ));
    }

    /**
     * @Route("/material/add", name="material_add")
     */
    public function addMaterialInfo(Request $request)
    {
        $warehouseCode = $this->getUser()->getDefaultWarehouse()->getWarehouseCode();
        $listMaterial = $this->getDoctrine()->getRepository(MaterialInfo::class)->findAll();
        $countRow = count($listMaterial) + 1;
        $date = date("y");
        $padCode = str_pad($countRow, 5, "0", STR_PAD_LEFT);
        $materialCode = "M" . $date . $padCode;

        $form = $this->createForm(MaterialInfoType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $materialCode = $this->getDoctrine()->getRepository(MaterialInfo::class)->findBy(array(
                'materialCode' => $form->get('materialCode')->getData()
            ));

            $materialObjArray = $this->getDoctrine()->getRepository(MaterialInfo::class)->findBy(array(
                'materialName' => $form->get('materialName')->getData()
            ));

            // if (count($materialCode) > 0) {
            //     $this->addFlash('error', 'รหัส material นี้มีอยู่ในระบบแล้ว กรุณาใส่รหัส Material อีกครั้ง');
            // } else
            if ($materialObjArray != null) {
                $this->addFlash('error', 'ชื่อ material นี้มีอยู่ในระบบแล้ว กรุณาระบุชื่อ Material อีกครั้ง');
            } else {
                //Insert Material
                $materialInfo = new MaterialInfo();
                $materialInfo->setMaterialCode($form->get('materialCode')->getData());
                $materialInfo->setMaterialName($form->get('materialName')->getData());
                $materialInfo->setMaterialFullName($form->get('materialFullName')->getData());
                $materialInfo->setMaterialStatus(1);

                //upload Image//////////////////////////
                if ($form->get('materialImage')->getData() != null) {
                    $file = $form->get('materialImage')->getData();
                    $images_l_directory = $this->getParameter('images_l_directory');
                    $images_s_directory = $this->getParameter('images_s_directory');
                    $filename = md5(uniqid()) . '.' . $file->guessExtension();
                    $file->move($images_l_directory, $filename);

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

                    $materialInfo->setmaterialImage($filename);
                }


                //Insert Material Transaction Data
                // $materialTrans = new MaterialTransaction($materialInfo);
                // $materialTrans->setMaterialTransactionCode('Create');
                // $materialTrans->setMaterialTransactionDate(new \DateTime("now", new \DateTimeZone('Asia/Bangkok')));
                // $materialTrans->setMaterialCode($materialInfo->getMaterialCode());
                // $materialTrans->setQuantity(0);
                // $materialTrans->setMaterialName($materialInfo->getMaterialName());

                $entityManager = $this->getDoctrine()->getManager();
                // $entityManager->persist($materialTrans);
                $entityManager->persist($materialInfo);
                $entityManager->flush();
//                if ($materialInfo) {
//                    $materialObj = $this->getDoctrine()->getRepository(MaterialInfo::class)->findOneBy(array(
//                        'materialCode' => $form->get('materialCode')->getData()
//                    ));
//                    // Insert Material Qty
//                    $materialQty = new MaterialQty($materialInfo);
//                    $materialQty->setMaterialCode($materialObj);
//                    $materialQty->setMaterialQty(0);
//                    $materialQty->setWarehouseCode($warehouseCode);
//                    $entityManager = $this->getDoctrine()->getManager();
//                    $entityManager->persist($materialQty);
//                    $entityManager->flush();
//                }
                $this->addFlash('success', 'บันทึกข้อมูลสำเร็จแล้ว');
                return $this->redirect($this->generateUrl('material_info'));
            }
        }

        return $this->render('material_info/addmaterial.html.twig', array(
            'formMaterial' => $form->createView(),
            'materialCode' => $materialCode
        ));
    }

    /**
     * @Route("/update/material/{id}", name="update_material")
     */
    public function updateMaterialInfo(Request $request, $id)
    {
        $materialInfo = $this->getDoctrine()->getRepository(MaterialInfo::class)->find($id);

        $form = $this->createForm(UpdateMaterialType::class, $materialInfo);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            ///////upload Image//////////////////////////
            if ($form->get('materialImage')->getData() != null) {
                $file = $form->get('materialImage')->getData();
                $images_l_directory = $this->getParameter('images_l_directory');
                $images_s_directory = $this->getParameter('images_s_directory');
                $filename = md5(uniqid()) . '.' . $file->guessExtension();
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
                $materialInfo->setmaterialImage($filename);
            }
            ////////////////////////////////////////
            $materialInfo->setMaterialStatus($form->get('materialStatus')->getData());
            $entityManager->flush();
            $this->addFlash('success', 'แก้ไขข้อมูล ' . $form->get('materialName')->getData() . ' สำเร็จแล้ว');

            return $this->redirect($this->generateUrl('material_info'));
        }

        return $this->render('material_info/updatematerial.html.twig', [
            'formMaterial' => $form->createView(),
            'materialImage' => $materialInfo->getMaterialImage()
        ]);
    }

    /***************************************************API PART***************************************************/

    /**
     * @Route("/material/checkmaterialName", name="material_checkmaterialName")
     */
    public function checkMaterialName(Request $request)
    {
        $materialName = $request->request->get('materialName');
        $materialObj = $this->getDoctrine()->getRepository(MaterialInfo::class)->findBy(array(
            'materialName' => $materialName
        ));

        if (count($materialObj) > 0) {
            return new JsonResponse(true);
        } else {
            return new JsonResponse(false);
        }
    }

    /**
     * @Route("/material/search", name="material_search")
     */
    public function searchMaterialInfo()
    {
        $output = array();
        $materialObj = $this->getDoctrine()->getRepository(MaterialInfo::class)->findBy(array(
            'materialStatus' => '1'
        ));

        for ($i = 0; $i < count($materialObj); $i++) {
            $output[$i] = array(
                "materialCode" => $materialObj[$i]->getMaterialCode(),
                "materialName" => $materialObj[$i]->getMaterialName()
            );
        }

        return new JsonResponse($output);
    }

    /**
     * @Route("/material_warehouse/search", name="material_warehouse_search")
     */
    public function searchMaterialInfoInWarehouse(
        MaterialQtyRepository $materialQtyRepository
    )
    {
        $warehouseCode = $this->getUser()->getDefaultWarehouse()->getWarehouseCode();
        $summaryMaterialInfo = $materialQtyRepository->summaryMaterialByWarehouse($warehouseCode);

        return $this->json($summaryMaterialInfo);
    }

    /**
     * @Route("/delete/material", name="delete_material")
     */
    public function deleteMaterialInfo(Request $request)
    {
        date_default_timezone_set("Asia/Bangkok");
        $data = $request->request->get('request');

        $materialObj = $this->getDoctrine()->getRepository(MaterialInfo::class)->find($data);

        //Insert Material Transaction Data
        $materialTrans = new MaterialTransaction($materialObj);
        $materialTrans->setMaterialTransactionCode('Delete');
        $materialTrans->setMaterialTransactionDate(new \DateTime("now", new \DateTimeZone('Asia/Bangkok')));
        $materialTrans->setMaterialCode($materialObj->getMaterialCode());
        $materialTrans->setMaterialName($materialObj->getMaterialName());
        $materialTrans->setQuantity(0);

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($materialTrans);
        $materialObj->setMaterialStatus(0);
        //$entityManager->remove($materialInfo);
        $entityManager->flush();
        $this->addFlash('success', 'ลบข้อมูล ' . $materialObj->getMaterialName() . ' สำเร็จแล้ว');

        return new JsonResponse(true);
    }

    public function generateMaterialCode()
    {
        $listMaterial = $this->getDoctrine()->getRepository(MaterialInfo::class)->findAll();
        $countRow = count($listMaterial) + 1;
        $date = date("y");
        $padCode = str_pad($countRow, 5, "0", STR_PAD_LEFT);
        $materialCode = "M" . $date . $padCode;

        return new JsonResponse($materialCode);
    }
}
