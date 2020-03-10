<?php

namespace App\Repository;

use App\Entity\CheckInItem;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method CheckInItem|null find($id, $lockMode = null, $lockVersion = null)
 * @method CheckInItem|null findOneBy(array $criteria, array $orderBy = null)
 * @method CheckInItem[]    findAll()
 * @method CheckInItem[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CheckInItemRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, CheckInItem::class);
    }

    // /**
    //  * @return CheckInItem[] Returns an array of CheckInItem objects
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
    public function findOneBySomeField($value): ?CheckInItem
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
    public function findByCheckInCode($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.checkInCode = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->getQuery()
            ->getResult()
            ;
    }
}
