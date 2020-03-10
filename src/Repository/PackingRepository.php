<?php

namespace App\Repository;

use App\Entity\Packing;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Packing|null find($id, $lockMode = null, $lockVersion = null)
 * @method Packing|null findOneBy(array $criteria, array $orderBy = null)
 * @method Packing[]    findAll()
 * @method Packing[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PackingRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Packing::class);
    }

    public function findSumMaterialByPackingId($packingBatchId)
    {
        return $this->createQueryBuilder('u')
            ->select('u.packingBatchId,u.startPackingBatchAt,u.warehouseCode,packMat.materialCode,sum(packMat.materialQty) as mQty, m.materialName')
            ->join('App\Entity\PackingBatchMaterial', 'packMat', 'WITH', 'u.packingBatchId = packMat.packingBatchId')
            ->join('App\Entity\MaterialInfo', 'm', 'WITH', 'packMat.materialCode = m.materialCode')
            ->andWhere('u.packingBatchId = :packingBatchId')
            ->setParameter('packingBatchId', $packingBatchId)
            ->groupBy('u.packingBatchId,u.startPackingBatchAt,u.warehouseCode,packMat.materialCode,m.materialName')
            ->getQuery()
            ->getResult();
    }

    public function findByPackingBatchId(string $query,$warehouseCode)
    {
        return $this->createQueryBuilder('p')
            ->select('p.packingBatchId,w.warehouseName,p.startPackingBatchAt,p.startPackingBatchEnd,p.packingStep, pim.id AS status')
            ->join('App\Entity\WarehouseInfo', 'w', 'WITH', 'p.warehouseCode = w.warehouseCode')
            ->leftJoin('App\Entity\PackingBatchMaterialIncomplete', 'pim', 'WITH', 'p.packingBatchId = pim.packingBatchId')
            ->Where('p.warehouseCode = :warehouseCode')
            ->andWhere('p.packingBatchId LIKE :query')
            ->setParameter('query', '%'.$query.'%')
            ->setParameter('warehouseCode', $warehouseCode)
            ->getQuery()
            ->getResult();
    }

    public function findListPacking($warehouseCode)
    {
        return $this->createQueryBuilder('p')
            ->select('p.packingBatchId,w.warehouseName,p.startPackingBatchAt,p.startPackingBatchEnd,p.packingStep, pim.id AS status')
            ->join('App\Entity\WarehouseInfo', 'w', 'WITH', 'p.warehouseCode = w.warehouseCode')
            ->leftJoin('App\Entity\PackingBatchMaterialIncomplete', 'pim', 'WITH', 'p.packingBatchId = pim.packingBatchId')
            ->andWhere('p.warehouseCode = :warehouseCode')
            ->setParameter('warehouseCode', $warehouseCode)
            ->OrderBy('p.startPackingBatchAt','DESC')
            ->getQuery()
            ->getResult();
    }

    public function findListPackingAdmin()
    {
        return $this->createQueryBuilder('p')
            ->select('p.packingBatchId,w.warehouseName,p.startPackingBatchAt,p.startPackingBatchEnd,p.packingStep, pim.id AS status')
            ->join('App\Entity\WarehouseInfo', 'w', 'WITH', 'p.warehouseCode = w.warehouseCode')
            ->leftJoin('App\Entity\PackingBatchMaterialIncomplete', 'pim', 'WITH', 'p.packingBatchId = pim.packingBatchId')
            ->OrderBy('p.startPackingBatchAt','DESC')
            ->getQuery()
            ->getResult();
    }

    public function findMaxPackingBatchId($warehouseCode)
    {
        return $this->createQueryBuilder('p')
            ->select('MAX(p.packingBatchId) AS packingBatchId')
            ->where('p.warehouseCode =:warehouseCode AND p.packingStep = 7')
            ->setParameter('warehouseCode', $warehouseCode)
            ->getQuery()
            ->getResult();
    }

//    public function summaryInCompleteMaterialByWarehouse($warehouse)
//    {
//        return $this->createQueryBuilder('aa')
//            ->select('aa.warehouseCode, aa.packingBatchId, bb.materialCode, cc.materialName, bb.materialQuantity')
//            ->leftJoin('App\Entity\PackingBatchMaterialIncomplete', 'bb', 'WITH', 'aa.packingBatchId = bb.packingBatchId')
//            ->leftJoin('App\Entity\MaterialInfo', 'cc', 'WITH', 'bb.materialCode = cc.materialCode')
//            ->andWhere('aa.warehouseCode = :warehouseCode')
//            ->setParameter('warehouseCode', $warehouse)
//            ->setFirstResult(0)
//            ->setMaxResults(1)
//            ->OrderBy('bb.packingBatchId','DESC')
//            ->getQuery()
//            ->getResult();
//    }

    // /**
    //  * @return Packing[] Returns an array of Packing objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('p.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Packing
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
