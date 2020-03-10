<?php

namespace App\Repository;

use App\Entity\BatchSku;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method BatchSku|null find($id, $lockMode = null, $lockVersion = null)
 * @method BatchSku|null findOneBy(array $criteria, array $orderBy = null)
 * @method BatchSku[]    findAll()
 * @method BatchSku[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BatchSkuRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, BatchSku::class);
    }

    // /**
    //  * @return BatchSku[] Returns an array of BatchSku objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('b.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?BatchSku
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
