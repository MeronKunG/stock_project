<?php

namespace App\Repository;

use App\Entity\PackingBatchSku;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method PackingBatchSku|null find($id, $lockMode = null, $lockVersion = null)
 * @method PackingBatchSku|null findOneBy(array $criteria, array $orderBy = null)
 * @method PackingBatchSku[]    findAll()
 * @method PackingBatchSku[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PackingBatchSkuRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, PackingBatchSku::class);
    }

    // /**
    //  * @return BatchSku[] Returns an array of BatchSku objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('b.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?BatchSku
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
    public function findSummarySkuByPackingBatchId($packingBatchId)
    {
        return $this->createQueryBuilder('pSku')
            ->select('pSku.skuCode, s.skuName, SUM(pSku.skuQty) as skuQty')
            ->join('App\Entity\SkuInfo', 's', 'WITH', 'pSku.skuCode = s.skuCode')
            ->Where('pSku.packingBatchId = :packingBatchId')
            ->setParameter('packingBatchId', $packingBatchId)
            ->GroupBy('pSku.skuCode, s.skuName')
            ->getQuery()
            ->getResult();
    }

    public function findSummaryMaterialByPackingBatchId($packingBatchId)
    {
        return $this->createQueryBuilder('aa')
            ->select('aa.skuCode, IDENTITY(bb.material) as materialCode, bb.quantity, aa.skuQty')
            ->join('App\Entity\Bom', 'bb', 'WITH', 'aa.skuCode = bb.skuCode')
            ->andWhere('aa.packingBatchId = :packingBatchId')
            ->setParameter('packingBatchId', $packingBatchId)
            ->getQuery()
            ->getResult();
    }
}
