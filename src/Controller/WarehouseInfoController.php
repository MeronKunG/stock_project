<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

use Knp\Component\Pager\PaginatorInterface;

use App\Entity\WarehouseInfo;
use App\Entity\WarehouseConfig;

use App\Form\WarehouseInfoType;
use App\Form\UpdateWarehouseType;

use App\Repository\WarehouseInfoRepository;
use App\Repository\WarehouseConfigRepository;

class WarehouseInfoController extends AbstractController
{
    /**
     * @Route("/warehouse/info", name="warehouse_info")
     */
    public function viewWarehouseInfo(Request $request, WarehouseInfoRepository $warehouseInfoRepository, PaginatorInterface $paginator)
    {
        date_default_timezone_set("Asia/Bangkok");
        $strSearch = $request->query->get('search');
        $listWarehouse = $this->getDoctrine()->getRepository(WarehouseInfo::class)->findAll();
        $result = $paginator->paginate(
            $listWarehouse,
            $request->query->getInt('page', 1),
            $request->query->getInt('limit', 10)
        );
        if ($strSearch != "" && mb_strlen($strSearch) >= 3) {
            $warehouseResult = $warehouseInfoRepository->findByWarehouseName($request->query->get('search'));
            $result = $paginator->paginate(
                $warehouseResult,
                $request->query->getInt('page', 1)/*page number*/,
                10/*limit per page*/
            );
        }

        return $this->render('warehouse_info/index.html.twig', array(
            'warehouses' => $result
        ));
    }

    /**
     * @Route("/warehouse/add", name="warehouse_add")
     */
    public function addWarehouseInfo(Request $request)
    {
        $form = $this->createForm(WarehouseInfoType::class);
        $form->handleRequest($request);
        $warehouseCode = $this->getDoctrine()->getRepository(WarehouseInfo::class)->findBy(array(
            'warehouseCode' => $form->get('warehouseCode')->getData()
        ));
        $warehouseName = $this->getDoctrine()->getRepository(WarehouseInfo::class)->findBy(array(
            'warehouseName' => $form->get('warehouseName')->getData()
        ));

        if (count($warehouseCode) > 0) {
            $this->addFlash('error', 'รหัส Warehouse นี้มีอยู่ในระบบแล้ว กรุณาใส่รหัส Warehouse อีกครั้ง');
        } elseif (count($warehouseName) > 0) {
            $this->addFlash('error', 'ชื่อ Warehouse นี้มีอยู่ในระบบแล้ว กรุณาระบุชื่อ Warehouse อีกครั้ง');
        } elseif ($form->isSubmitted() && $form->isValid()) {
            /* Insert Warehouse */
            $warehouseInfo = new WarehouseInfo();
            $warehouseInfo->setWarehouseCode($form->get('warehouseCode')->getData());
            $warehouseInfo->setWarehouseName($form->get('warehouseName')->getData());
            $warehouseInfo->setWarehouseStatus(1);

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($warehouseInfo);
            $entityManager->flush();
            $this->addFlash('success', 'บันทึกข้อมูลสำเร็จแล้ว');
            return $this->redirect($this->generateUrl('warehouse_info'));
        }

        return $this->render('warehouse_info/addwarehouse.html.twig', array(
            'formWarehouse' => $form->createView(),
        ));
    }

    /**
     * @Route("/update/warehouse/{id}", name="update_warehouse")
     */
    public function updateWarehouseInfo(Request $request, $id, WarehouseConfigRepository $repWarehouseConfig)
    {

        $warehouseInfo = $this->getDoctrine()->getRepository(WarehouseInfo::class)->find($id);
        $warehouseConfig = $repWarehouseConfig->findAllZipcode($id);

        $form = $this->createForm(UpdateWarehouseType::class, $warehouseInfo);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $zipcodes = $repWarehouseConfig->findBy(array('warehouseCode' => $id));
            $entityManager = $this->getDoctrine()->getManager();
            foreach ($zipcodes as $zipcode) {
                $entityManager->remove($zipcode);
            }
            $entityManager->flush();

            $zipcodeCon = $form->get('hiddenForm')->getData();
            $zipcodeList = json_decode($zipcodeCon, true);

            if(count($zipcodeList)!=0){
                foreach ($zipcodeList as $item) {
                    $warehouseConfig = new WarehouseConfig();
                    $warehouseConfig->setWarehouseCode($id);
                    $warehouseConfig->setZipcode($item['zipCode']);
                    $entityManager = $this->getDoctrine()->getManager();
                    $entityManager->persist($warehouseConfig);
                    $entityManager->flush();
                }
            }
            $entityManager = $this->getDoctrine()->getManager();
            $warehouseInfo->setWarehouseStatus($form->get('warehouseStatus')->getData());
            $entityManager->flush();
            $this->addFlash('success', 'แก้ไขข้อมูล ' . $form->get('warehouseName')->getData() . ' สำเร็จแล้ว');

            return $this->redirect($this->generateUrl('warehouse_info'));
        }

        return $this->render('warehouse_info/updatewarehouse.html.twig', [
            'formWarehouse' => $form->createView(),
            'id' => $id,
            'warehouseItem' => $this->json($warehouseConfig)
        ]);
    }

    /****************************************************API PART****************************************************/

    /**
     * @Route("/delete/warehouse", name="delete_warehouse")
     */
    public function deleteWarehouseInfo(Request $request)
    {
        $data = $request->request->get('request');
        $warehouseInfo = $this->getDoctrine()->getRepository(WarehouseInfo::class)->find($data);

        $entityManager = $this->getDoctrine()->getManager();
        //$entityManager->remove($warehouseInfo);
        $warehouseInfo->setWarehouseStatus(0);
        $entityManager->flush();
        $this->addFlash('success', 'ลบข้อมูลสำเร็จแล้ว');

        return new JsonResponse(true);
    }

    /**
     * @Route("/service/checkZipCode", name="service_warehouse")
     */
    public function checkZipCode(Request $request, WarehouseConfigRepository $repWarehouseConfig)
    {
        $data = $request->query->get('id');
        $warehouseConfig = $repWarehouseConfig->findAllZipcode($data);

        return $this->json($warehouseConfig);
    }

    /**
     * @Route("/select/zipcode/config", name="select_check_zipcode")
     */
    public function selectZipcodeConfig(Request $request, WarehouseConfigRepository $repWarehouseConfig)
    {
        $data = $request->query->get('zipcode');
        $warehouseConfig = $repWarehouseConfig->count(array('zipcode' => $data));
        return $this->json($warehouseConfig);
    }
}
