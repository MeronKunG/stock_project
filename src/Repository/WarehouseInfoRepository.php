<?php

namespace App\Repository;

use App\Entity\WarehouseInfo;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method WarehouseInfo|null find($id, $lockMode = null, $lockVersion = null)
 * @method WarehouseInfo|null findOneBy(array $criteria, array $orderBy = null)
 * @method WarehouseInfo[]    findAll()
 * @method WarehouseInfo[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class WarehouseInfoRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, WarehouseInfo::class);
    }

    // /**
    //  * @return WarehouseInfo[] Returns an array of WarehouseInfo objects
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
    public function findOneBySomeField($value): ?WarehouseInfo
    {
        return $this->createQueryBuilder('w')
            ->andWhere('w.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
    public function findByWarehouseName(string $query)
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.warehouseName LIKE :query')
            ->setParameter('query', '%'.$query.'%')
            ->getQuery()
            ->getResult();
    }
}
