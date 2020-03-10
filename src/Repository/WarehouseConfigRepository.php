<?php

namespace App\Repository;

use App\Entity\WarehouseConfig;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method WarehouseConfig|null find($id, $lockMode = null, $lockVersion = null)
 * @method WarehouseConfig|null findOneBy(array $criteria, array $orderBy = null)
 * @method WarehouseConfig[]    findAll()
 * @method WarehouseConfig[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class WarehouseConfigRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, WarehouseConfig::class);
    }

    public function findAllZipcode($warehouseCode)
    {
        return $this->createQueryBuilder('u')
            ->select('u.zipcode')
            ->Where('u.warehouseCode = :query')
            ->setParameter('query', $warehouseCode)
            ->getQuery()
            ->getResult();
    }

    public function checkWarehouseConfig($zipcode)
    {
        return $this->createQueryBuilder('u')
            ->select('u.warehouseCode,u.zipcode')
            ->Where('u.zipcode = :query')
            ->setParameter('query', $zipcode)
            ->getQuery()
            ->getResult();
    }

    // /**
    //  * @return WarehouseConfig[] Returns an array of WarehouseConfig objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('w')
            ->andWhere('w.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('w.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?WarehouseConfig
    {
        return $this->createQueryBuilder('w')
            ->andWhere('w.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
