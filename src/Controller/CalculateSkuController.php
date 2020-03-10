<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

use App\Entity\SkuInfo;
use App\Entity\Bom;
use App\Entity\MaterialQty;

use App\Repository\MaterialQtyRepository;
use App\Repository\MaterialTransactionRepository;
use App\Repository\MaterialQtyReserveRepository;
use App\Repository\BomRepository;
use App\Repository\SkuInfoRepository;

class CalculateSkuController extends AbstractController
{
    /**
     * @Route("/sku/available/api", methods={"GET"})
     */
    public function availableSkuApi(
        Request $request,
        MaterialQtyRepository $materialQtyRepository,
        MaterialQtyReserveRepository $materialQtyReserveRepository,
        BomRepository $bomRepository,
        SkuInfoRepository $repSkuInfo
    ) {
        $output=array();
 
        $skuCode = $request->query->get('skuCode');
        $warehouseCode = $request->query->get('warehouseCode');
        
        if ($skuCode === null && $warehouseCode=== null) {
            return $this->json(false);
        } else {
            $skuObj = $repSkuInfo->findOneBy(array('skuCode' => $skuCode));

            /****************************Calculate Available SKU****************************/
            /* 1.หาของใน BOM */
            $bomObj = $bomRepository->findBomQtyBySku($skuObj);
            for ($i = 0; $i < count($bomObj); $i++) {
                $materialCodeArray[$i] =  $bomObj[$i]->getMaterial()->getMaterialCode();
                $bomQtyArray[$i] = $bomObj[$i]->getQuantity();
            }
            /* 2.เช็คว่า แต่ละMaterial มีเท่าไหร่ ใน Summary */
            $materialQtyObj = $materialQtyRepository->sumQtyByWarehouse($warehouseCode, $materialCodeArray);
            // $materialQtyObj =$materialTransactionRepository->sumQtyByWarehouse($warehouseCode, $materialCodeArray);
        
            /* 3.หา Material ที่มีจำนวนน้อยที่สุด */
            for ($q = 0; $q < count($materialQtyObj); $q++) {
                /* 4. qty(sum) / qty(m.bom) return available(int) */
                $ratioQty[$q]=$materialQtyObj[$q]["materialQty"]/$bomQtyArray[$q];
            }
            $available=floor(min($ratioQty));

            $materialQtyReserve=$materialQtyReserveRepository->findReserveQtyInWarehouse($warehouseCode, $materialCodeArray);
            for ($r = 0; $r < count($materialQtyReserve); $r++) {
                $ratioQtyReserve[$r]=$materialQtyReserve[$r]["quantity"]/$bomQtyArray[$r];
            }
            $qtyReserve=floor(min($ratioQtyReserve));
        
            /*******************************************************************************/
            $output = array(
                "skuCode" => $skuObj->getSkuCode(),
                "skuName" => $skuObj->getSkuName(),
                "available" => $available-$qtyReserve
            );
            return $this->json($output);
        }
    }
}
