<?php

namespace App\Repository;

use App\Entity\SkuInfo;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method SkuInfo|null find($id, $lockMode = null, $lockVersion = null)
 * @method SkuInfo|null findOneBy(array $criteria, array $orderBy = null)
 * @method SkuInfo[]    findAll()
 * @method SkuInfo[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SkuInfoRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, SkuInfo::class);
    }

    // /**
    //  * @return SkuInfo[] Returns an array of SkuInfo objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('s.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?SkuInfo
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
    public function findAllSkuQuery(string $query)
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.skuCode LIKE :query OR u.skuName LIKE :query')
            ->setParameter('query', '%'.$query.'%')
            ->getQuery()
            ->getResult();
    }
}
