<?php

namespace App\Repository;

use App\Entity\CourierInfo;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method CourierInfo|null find($id, $lockMode = null, $lockVersion = null)
 * @method CourierInfo|null findOneBy(array $criteria, array $orderBy = null)
 * @method CourierInfo[]    findAll()
 * @method CourierInfo[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CourierInfoRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, CourierInfo::class);
    }

    // /**
    //  * @return CourierInfo[] Returns an array of CourierInfo objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?CourierInfo
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
    public function findAllCourierQuery(string $query)
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.courierCode LIKE :query OR u.courierName LIKE :query')
            ->setParameter('query', '%'.$query.'%')
            ->getQuery()
            ->getResult();
    }
}
