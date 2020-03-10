<?php

namespace App\Repository;

use App\Entity\ParcelSize;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method ParcelSize|null find($id, $lockMode = null, $lockVersion = null)
 * @method ParcelSize|null findOneBy(array $criteria, array $orderBy = null)
 * @method ParcelSize[]    findAll()
 * @method ParcelSize[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ParcelSizeRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ParcelSize::class);
    }

    // /**
    //  * @return ParcelSize[] Returns an array of ParcelSize objects
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
    public function findOneBySomeField($value): ?ParcelSize
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
