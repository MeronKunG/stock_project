<?php

namespace App\Repository;

use App\Entity\MaterialQtyReserve;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method MaterialQtyReserve|null find($id, $lockMode = null, $lockVersion = null)
 * @method MaterialQtyReserve|null findOneBy(array $criteria, array $orderBy = null)
 * @method MaterialQtyReserve[]    findAll()
 * @method MaterialQtyReserve[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MaterialQtyReserveRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, MaterialQtyReserve::class);
    }

    public function findReserveQtyByWarehouse(string $warehouseCode, string $materialCode)
    {
        return $this->createQueryBuilder('u')
            ->select('u.quantity')
            ->andWhere('u.materialCode = :materialCode')
            ->andWhere('u.warehouseCode = :warehouseCode')
            ->setParameter('materialCode', $materialCode)
            ->setParameter('warehouseCode', $warehouseCode)
            ->getQuery()
            ->getResult();
    }

    public function findReserveQtyInWarehouse($warehouseCode, $materialCode)
    {
        return $this->createQueryBuilder('u')
            ->select('u.quantity')
            ->andWhere('u.materialCode IN (:materialCode)')
            ->andWhere('u.warehouseCode IN (:warehouseCode)')
            ->setParameter('materialCode', $materialCode)
            ->setParameter('warehouseCode', $warehouseCode)
            ->getQuery()
            ->getResult();
    }

    // /**
    //  * @return MaterialQtyReserve[] Returns an array of MaterialQtyReserve objects
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
    public function findOneBySomeField($value): ?MaterialQtyReserve
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
