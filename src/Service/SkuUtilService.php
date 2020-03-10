<?php

namespace App\Service;

use App\Repository\SkuInfoRepository;

class SkuUtilService
{
    private $skuInfoRepository;
    private $api945HoldingService;

    public function __construct(SkuInfoRepository $skuInfoRepository, Api945HoldingService $api945HoldingService)
    {
        $this->skuInfoRepository = $skuInfoRepository;
        $this->api945HoldingService = $api945HoldingService;
    }


    public function checkSku()
    {
        $total = $this->skuInfoRepository->count([]);
        $all_product = $this->api945HoldingService->listAllProduct();
        $product_global_id_list = [];
        foreach ($all_product['result'] as $product) {
            if (!in_array($product['product_global_id'], $product_global_id_list)) {
                array_push($product_global_id_list, $product['product_global_id']);
            }
        }
        $output = [
            "total" => $total,
            "945HoldingProductCount" => count($all_product['result']),
            "945HoldingGlobalProductCount" => count($product_global_id_list),
            "945HoldingProduct" => $all_product
        ];
        return $output;
    }
}
