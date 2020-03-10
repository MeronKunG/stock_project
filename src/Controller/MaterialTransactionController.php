<?php

namespace App\Controller;

use App\Entity\WarehouseInfo;
use App\Repository\PackingBatchMaterialInCompleteRepository;
use App\Repository\PackingRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

use Knp\Component\Pager\PaginatorInterface;

use Symfony\Component\HttpFoundation\JsonResponse;

use App\Repository\MaterialTransactionRepository;
use App\Repository\MaterialQtyRepository;

use App\Entity\MaterialTransaction;
use App\Entity\MaterialInfo;

use App\Form\MaterialTransactionType;
use App\Entity\User;

use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class MaterialTransactionController extends AbstractController
{
    /**
     * @Route("/material/transaction", name="material_transaction")
     */
    public function viewTransaction(
        Request $request,
        MaterialTransactionRepository $repMaterialTransaction,
        MaterialQtyRepository $repMaterialQty,
        PaginatorInterface $paginator
    )
    {
        date_default_timezone_set("Asia/Bangkok");
        $materialCode = $request->get('materialCode');
        $listTransaction = $repMaterialTransaction->findByMaterialCode($materialCode);
        if ($listTransaction == null) {
            echo "<script language='javascript' type='text/javascript' > alert('ไม่พบข้อมูล'); 
            document.location.href='/material/info'</script>";
//            return $this->redirect($this->generateUrl('material_info'));
        } else {
            $result = $paginator->paginate(
                $listTransaction,
                $request->query->getInt('page', 1),
                $request->query->getInt('limit', 10)
            );

            $sumCheckIn = $repMaterialTransaction->sumQtyByMaterialCode($materialCode, "CIN");
            $sumCheckOut = $repMaterialTransaction->sumQtyByMaterialCode($materialCode, "COT");
            $sum = $sumCheckIn[0]["sumQty"] - $sumCheckOut[0]["sumQty"];
            return $this->render('material_transaction/index.html.twig', array(
                'items' => $result,
                'materialName' => $listTransaction[0]["materialName"],
                'sum' => $sum
            ));
        }
    }

    /**
     * @Route("/material/transaction/summary", name="view_summary")
     */
    public function summaryTransaction(Request $request,
                                       MaterialQtyRepository $materialQtyRepository,
                                       AuthorizationCheckerInterface $authChecker,
                                       PackingRepository $packingRepository,
                                       PackingBatchMaterialInCompleteRepository $batchMaterialInCompleteRepository
    )
    {
        $warehouseCode = $this->getUser()->getDefaultWarehouse()->getWarehouseCode();

        if (true === $authChecker->isGranted('ROLE_ADMIN')) {
            $summaryMaterialInfo = $materialQtyRepository->summaryMaterialAdmin();
        } else {
            $summaryMaterialInfo = $materialQtyRepository->summaryMaterialByWarehouse($warehouseCode);
        }
        $tableData = [];
        $maxPackingId = $packingRepository->findMaxPackingBatchId($warehouseCode);

        if (!empty($maxPackingId)) {
            $tableData = $batchMaterialInCompleteRepository->findTableDataByPackingBatchId($maxPackingId[0]['packingBatchId']);
            foreach ($tableData as $key => $table) {
                $tableData[$key]['warehouseCode'] = $warehouseCode;
            }
        }

        return $this->render('transaction_summary/index.html.twig', array(
            'items' => $summaryMaterialInfo,
            'inCompleteItems' => $tableData
            // 'formSummary' => $form->createView()
        ));
    }

    /**
     * @Route("/material/transaction/resultsummary", name="view_resultsummary")
     */
    public
    function viewResultSummary(
        Request $request,
        MaterialTransactionRepository $materialTransactionRepository,
        PaginatorInterface $paginator
    )
    {
        $summaryMaterialInfo = $materialTransactionRepository->summaryMaterialInfo();
        $result = $paginator->paginate(
            $summaryMaterialInfo,
            $request->query->getInt('page', 1)/*page number*/,
            10/*limit per page*/
        );

        return new JsonResponse($summaryMaterialInfo);
    }

    /**
     * @Route("/material/transaction/searchsummary/{materialName}", name="search_resultsummary")
     */
    public function searchResultSummary(
        Request $request,
        MaterialTransactionRepository $materialTransactionRepository,
        $materialName
    )
    {
        $summaryMaterialInfo = $materialTransactionRepository->findAllMatching($materialName);
        return new JsonResponse($summaryMaterialInfo);
    }

    /**
     *
     * @Route("/material/transaction/search", name="ajax_search",methods={"GET"})
     */
    public
    function searchAction(Request $request, MaterialTransactionRepository $materialTransactionRepository)
    {
        $output = array();
        $requestString = $request->query->get('q');

        $materialTransactions = $materialTransactionRepository->findEntitiesByString($requestString);
        $sumQty = $materialTransactionRepository->sumQTY($requestString);

        $output = array(
            'materialTrans' => $materialTransactions,
            'sumQTY' => $sumQty
        );
        return new JsonResponse($output);
    }

    /**
     *
     * @Route("/material/transaction/searchvalue", name="ajax_search_value")
     */
    public
    function searchActionValue(Request $request)
    {
        $output = array();
        // $requestString = $request->query->get('q');
        $Obj = $this->getDoctrine()->getRepository(MaterialInfo::class)->findAll();
        for ($i = 0; $i < count($Obj); $i++) {
            $output[$i] = array(
                'materialCode' => $Obj[$i]->getMaterialCode(),
                'materialName' => $Obj[$i]->getMaterialName(),
            );
        }
        return new JsonResponse($output);
    }
}
