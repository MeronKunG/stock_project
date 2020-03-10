<?php

namespace App\Repository;

use App\Entity\PackingBatchMaterialIncomplete;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method PackingBatchMaterialIncomplete|null find($id, $lockMode = null, $lockVersion = null)
 * @method PackingBatchMaterialIncomplete|null findOneBy(array $criteria, array $orderBy = null)
 * @method PackingBatchMaterialIncomplete[]    findAll()
 * @method PackingBatchMaterialIncomplete[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PackingBatchMaterialInCompleteRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, PackingBatchMaterialIncomplete::class);
    }

    public function deleteDataByPackingBatchId($packingBatchId)
    {
        $query = $this->createQueryBuilder('p')
            ->delete()
            ->where('p.packingBatchId =:packingBatchId')
            ->setParameter('packingBatchId', $packingBatchId)
            ->getQuery();

        return $query->execute();
    }

    public function findTableDataByPackingBatchId($packingBatchId)
    {
        $query = $this->createQueryBuilder('aa')
            ->select('aa.materialCode, aa.materialQuantity, cc.materialName')
            ->leftJoin('App\Entity\MaterialInfo', 'cc', 'WITH',' aa.materialCode = cc.materialCode')
            ->where('aa.packingBatchId =:packingBatchId')
            ->setParameter('packingBatchId', $packingBatchId)
            ->getQuery();

        return $query->execute();
    }

//    public function summaryInCompleteMaterialByWarehouse($warehouse)
//    {
//        $dql = 'SELECT aa.materialCode, aa.materialQuantity, cc.materialName FROM App\Entity\PackingBatchMaterialIncomplete aa LEFT JOIN App\Entity\MaterialInfo cc WITH aa.materialCode = cc.materialCode WHERE aa.packingBatchId = (SELECT max(packing.packingBatchId) FROM App\Entity\Packing packing WHERE packing.warehouseCode = :warehouse AND packing.packingStep = 7)';
//        $query = $this->getEntityManager()->createQuery($dql)->setParameter('warehouse', $warehouse);
//        return $query->execute();
//    }
//        return $this->cr('aa')
//            ->select('aa.materialCode')
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

}
