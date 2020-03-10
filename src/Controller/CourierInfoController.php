<?php

namespace App\Controller;

use App\Repository\CourierInfoRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

use Knp\Component\Pager\PaginatorInterface;

use App\Entity\CourierInfo;

use App\Form\CourierInfoType;
use App\Form\UpdateCourierType;

class CourierInfoController extends AbstractController
{
    /**
     * @Route("/courier/info", name="courier_info")
     */
    public function viewCourierInfo(
        Request $request,
        CourierInfoRepository $courierInfoRepository,
        PaginatorInterface $paginator
    ) {
        date_default_timezone_set("Asia/Bangkok");
        $strSearch = $request->query->get('search');
        $listCourier = $this->getDoctrine()->getRepository(CourierInfo::class)->findAll();

        $results = $paginator->paginate(
            $listCourier,
            $request->query->getInt('page', 1),
            $request->query->getInt('limit', 10)
        );
        if ($strSearch != "" && mb_strlen($strSearch) >= 3) {
            $courierResult = $courierInfoRepository->findAllCourierQuery($request->query->get('search'));
            $results = $paginator->paginate(
                $courierResult,
                $request->query->getInt('page', 1)/*page number*/,
                10/*limit per page*/
            );
        }

        return $this->render('courier_info/index.html.twig', array(
            'couriers' => $results
        ));
    }

    /**
     * @Route("/courier/add", name="courier_add")
     */
    public function addCourierInfo(Request $request)
    {
        $form = $this->createForm(CourierInfoType::class);
        $form->handleRequest($request);

        $courierCode = $this->getDoctrine()->getRepository(CourierInfo::class)->findBy(array(
            'courierCode' => $form->get('courierCode')->getData()
        ));
        $courierName = $this->getDoctrine()->getRepository(CourierInfo::class)->findBy(array(
            'courierName' => $form->get('courierName')->getData()
        ));
        $courierPrefix = $this->getDoctrine()->getRepository(CourierInfo::class)->findBy(array(
            'courierPrefix' => $form->get('courierPrefix')->getData()
        ));
        $courierPrefixToUpper = strtoupper($form->get('courierPrefix')->getData());

        if (count($courierCode) > 0) {
            $this->addFlash('error', 'รหัส Courier นี้มีอยู่ในระบบแล้ว กรุณาใส่รหัส Courier อีกครั้ง');
        } elseif (count($courierName) > 0) {
            $this->addFlash('error', 'ชื่อ Courier นี้มีอยู่ในระบบแล้ว กรุณาระบุชื่อ Courier อีกครั้ง');
        } elseif (count($courierPrefix) > 0) {
            $this->addFlash('error', 'ชื่อย่อ Courier นี้มีอยู่ในระบบแล้ว กรุณาระบุชื่อย่อ Courier อีกครั้ง');
        } elseif ($form->isSubmitted() && $form->isValid()) {
            /* Insert Courier */
            $courierInfo = new CourierInfo();
            $courierInfo->setCourierCode($form->get('courierCode')->getData());
            $courierInfo->setCourierName($form->get('courierName')->getData());
            $courierInfo->setCourierPrefix($courierPrefixToUpper);
            $courierInfo->setSizeCode($form->get('sizeCode')->getData());
            $courierInfo->setCourierStatus(1);

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($courierInfo);
            $entityManager->flush();
            $this->addFlash('success', 'บันทึกข้อมูลสำเร็จแล้ว');
            return $this->redirect($this->generateUrl('courier_info'));
        }

        return $this->render('courier_info/add.html.twig', array(
            'formCourier' => $form->createView(),
        ));
    }

    /**
     * @Route("/update/courier/{id}", name="update_courier")
     */
    public function updateCourierInfo(Request $request, $id)
    {
        $courierInfo = $this->getDoctrine()->getRepository(CourierInfo::class)->find($id);

        $form = $this->createForm(UpdateCourierType::class, $courierInfo);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();

            $courierInfo->setSizeCode($form->get('sizeCode')->getData());
            $courierInfo->setCourierStatus($form->get('courierStatus')->getData());

            $entityManager->flush();
            $this->addFlash('success', 'แก้ไขข้อมูล ' . $form->get('courierName')->getData() . ' สำเร็จแล้ว');

            return $this->redirect($this->generateUrl('courier_info'));
        }

        return $this->render('courier_info/updatecourier.html.twig', [
            'formCourier' => $form->createView(),
        ]);
    }
}
