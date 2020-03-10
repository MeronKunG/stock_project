<?php

namespace App\Command;

use App\Entity\Bom;
use App\Entity\MaterialInfo;
use App\Entity\SkuInfo;
use App\Service\Api945HoldingService;
use App\Service\SkuUtilService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Stopwatch\Stopwatch;

class UtilsCommand extends Command
{
    protected static $defaultName = 'app:utils';

    private $skuUtilService;
    private $entityManager;
    private $api945HoldingService;

    public function __construct(
        EntityManagerInterface $entityManager,
        Api945HoldingService $api945HoldingService,
        SkuUtilService $skuUtilService)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->api945HoldingService = $api945HoldingService;
        $this->skuUtilService = $skuUtilService;
    }

    protected function configure()
    {
        $this
            ->setDescription('Utilities')
            ->addArgument('action', InputArgument::REQUIRED, 'Action: sync-sku');
    }

    /**
     * @param SymfonyStyle $io
     */

    private function syncSkuAction($io)
    {

        $stop_watch = new Stopwatch();

        $material = $this->entityManager->getRepository(MaterialInfo::class)->find('0000000000');
        if (!$material) {
            $io->error("Unable to find dummy material");
	    $material = new MaterialInfo();
	    $material->setMaterialCode('0000000000');
	    $material->setMaterialName('Dummy');
            $this->entityManager->persist($material);
	    $this->entityManager->flush();

        }

        $stop_watch->start("api");
        $all_product = $this->api945HoldingService->listAllProduct();
        $stop_watch->stop("api");

        $stop_watch->start("process");
        $io->writeln("Product count : " . count($all_product['result']));
        $unique_global_product_list = [];
        $new_product_list = [];
        foreach ($all_product['result'] as $product) {
            $sku_id = "" . $product['product_id'];
            $sku_id = str_pad($sku_id, 10, "0", STR_PAD_LEFT);
            if (!in_array($sku_id, $unique_global_product_list)) {
                array_push($unique_global_product_list, $sku_id);

                if (!$this->entityManager->getRepository(SkuInfo::class)->find($sku_id)) {
                    array_push($new_product_list, $sku_id);
                    $sku = new SkuInfo();

                    $sku->setSkuCode($sku_id);
                    $sku->setSkuName($product['product_name']);
                    $sku->setSkuStatus(1);

                    $this->entityManager->persist($sku);

                    $bom = new Bom();
                    $bom->setSkuCode($sku);
                    $bom->setMaterial($material);
                    $bom->setQuantity(1);
                    $this->entityManager->persist($bom);

                }
            }
        }
        $this->entityManager->flush();

        $io->writeln("Unique global product count : " . count($unique_global_product_list));
        $io->writeln("New product count : " . count($new_product_list));
//        $io->note('Load 945holding production list');
//        $skuSummary = $this->skuUtilService->checkSku();
////        $io->write(json_encode($skuSummary));
//        $io->write(sprintf("Product count : %d\n", $skuSummary['945HoldingProductCount']));
//        $io->write(sprintf("Global product count : %d\n", $skuSummary['945HoldingGlobalProductCount']));
        $ev = $stop_watch->stop('process');
        $io->writeln("API : " . $stop_watch->getEvent('api'));
        $io->writeln("Process : " . $stop_watch->getEvent('process'));
        $io->success("Finish");
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $action = $input->getArgument('action');
        if ($action) {
            switch ($action) {
                case "sync-sku":
                    $this->syncSkuAction($io);
                    break;
                default:
                    $io->error(sprintf("Unknown action %s", $action));
                    break;
            }
        }

    }
}
