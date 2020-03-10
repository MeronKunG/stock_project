<?php

namespace App\Repository;

use App\Entity\LogDataBestApi;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method LogDataBestApi|null find($id, $lockMode = null, $lockVersion = null)
 * @method LogDataBestApi|null findOneBy(array $criteria, array $orderBy = null)
 * @method LogDataBestApi[]    findAll()
 * @method LogDataBestApi[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LogDataBestApiRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LogDataBestApi::class);
    }

    // /**
    //  * @return LogDataBestApi[] Returns an array of LogDataBestApi objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('l')
            ->andWhere('l.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('l.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?LogDataBestApi
    {
        return $this->createQueryBuilder('l')
            ->andWhere('l.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
