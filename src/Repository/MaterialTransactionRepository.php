<?php

namespace App\Repository;

use App\Entity\MaterialTransaction;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/* src/AppBundle/Repository/MaterialTransactionRepository */

/**
 * @method MaterialTransaction|null find($id, $lockMode = null, $lockVersion = null)
 * @method MaterialTransaction|null findOneBy(array $criteria, array $orderBy = null)
 * @method MaterialTransaction[]    findAll()
 * @method MaterialTransaction[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MaterialTransactionRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, MaterialTransaction::class);
    }

    public function findByMaterialCode(string $materialCode)
    {
        return $this->createQueryBuilder('u')
            ->select('u.materialTransactionCode,u.materialTransactionDate,w.warehouseName,u.transactionType,u.referenceId,u.materialName,u.quantity')
            ->join('App\Entity\WarehouseInfo', 'w', 'WITH', 'u.warehouseCode = w.warehouseCode')
            ->andWhere('u.materialCode = :materialCode')
            ->setParameter('materialCode', $materialCode)
            ->OrderBy('u.materialTransactionDate','DESC')
            ->getQuery()
            ->getResult();
    }

    public function summaryMaterialInfo($warehouseCode)
    {
        return $this->createQueryBuilder('u')
            ->select('u.materialCode,u.materialName,SUM(u.quantity) as quantity')
            ->where('u.warehouseCode =:warehouseCode')
            ->setParameter('warehouseCode', $warehouseCode)
            ->addGroupBy('u.materialCode,u.materialName')
            ->getQuery()
            ->getResult();
    }

    public function findAllMatching(string $query)
    {
        return $this->createQueryBuilder('u')
            ->select('u.materialCode,u.materialName,SUM(u.quantity) as quantity')
            ->andWhere('u.materialName LIKE :query')
            ->setParameter('query', '%' . $query . '%')
            ->getQuery()
            ->getResult();
    }

    public function sumQTY(string $query)
    {
        return $this->createQueryBuilder('u')
            ->select('u.materialCode,SUM(u.quantity) as quantity')
            ->addGroupBy('u.materialCode')
            ->andWhere('u.materialCode = :query')
            ->setParameter('query', $query)
            ->addGroupBy('u.materialCode')
            ->getQuery()
            ->getResult();
    }

    public function sumQtyByWarehouse(string $warehouseCode, $materialCode)
    {
        return $this->createQueryBuilder('u')
            ->select('u.materialCode, SUM(u.quantity) as quantity')
            ->andWhere('u.materialCode IN (:materialCode)')
            ->andWhere('u.warehouseCode = :warehouseCode')
            ->setParameter('materialCode', $materialCode)
            ->setParameter('warehouseCode', $warehouseCode)
            ->addGroupBy('u.materialCode')
            ->getQuery()
            ->getResult();
    }

    public function sumQtyByMaterialCode(string $materialCode, string $transactionType)
    {
        return $this->createQueryBuilder('u')
            ->select('sum(u.quantity) as sumQty')
            ->andWhere('u.materialCode = :materialCode')
            ->andWhere('u.transactionType = :transactionType')
            ->setParameter('materialCode', $materialCode)
            ->setParameter('transactionType', $transactionType)
            ->getQuery()
            ->getResult();
    }

    // /**
    //  * @return MaterialTransaction[] Returns an array of MaterialTransaction objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('m.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?MaterialTransaction
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
