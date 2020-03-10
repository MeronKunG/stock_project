<?php

namespace App\DataFixtures;

use App\Entity\SkuInfo;
use App\Entity\Bom;
use App\Service\Api945HoldingService;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class SkuFixtures extends Fixture implements DependentFixtureInterface
{
    private $api945HoldingService;

    public function __construct(Api945HoldingService $api945HoldingService)
    {
        $this->api945HoldingService = $api945HoldingService;
    }

    public function load(ObjectManager $manager)
    {

        $all_product = $this->api945HoldingService->listAllProduct();
        $material = $this->getReference("DUMMY_MATERIAL_REF");
        foreach ($all_product['result'] as $product) {
            $sku_id = "" . $product['product_global_id'];
            $sku_id = str_pad($sku_id, 10, "0", STR_PAD_LEFT);
            if (!$manager->getRepository(SkuInfo::class)->find($sku_id)) {
                $sku = new SkuInfo();

                $sku->setSkuCode($sku_id);
                $sku->setSkuName($product['product_name']);

                $manager->persist($sku);
                $manager->flush();

                $bom = new Bom();
                $bom->setSkuCode($sku);
                $bom->setMaterial($material);
                $bom->setQuantity(1);
                $manager->persist($bom);
                $manager->flush();

            }


        }

    }

    public function getDependencies()
    {
        return [MaterialFixtures::class];
    }
}
