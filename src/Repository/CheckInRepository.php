<?php

namespace App\Repository;

use App\Entity\CheckIn;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method CheckIn|null find($id, $lockMode = null, $lockVersion = null)
 * @method CheckIn|null findOneBy(array $criteria, array $orderBy = null)
 * @method CheckIn[]    findAll()
 * @method CheckIn[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CheckInRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, CheckIn::class);
    }

    public function findCheckInByWarehouseCode($warehouseCode)
    {
        return $this->createQueryBuilder('u')
            ->select('count(u.checkInCode) as countCheckIn')
            ->Where('u.warehouseCode = :warehouseCode')
            ->setParameter('warehouseCode', $warehouseCode)
            ->OrderBy('u.checkInDate','DESC')
            ->getQuery()
            ->getResult();
    }

    public function findListCheckInAdmin()
    {
        return $this->createQueryBuilder('u')
            ->join('App\Entity\WarehouseInfo', 'w', 'WITH', 'u.warehouseCode = w.warehouseCode')
            ->OrderBy('u.checkInDate','DESC')
            ->getQuery()
            ->getResult();
    }

    public function findListCheckInUser($warehouseCode)
    {
        return $this->createQueryBuilder('u')
            ->join('App\Entity\WarehouseInfo', 'w', 'WITH', 'u.warehouseCode = w.warehouseCode')
            ->Where('u.warehouseCode = :warehouseCode')
            ->setParameter('warehouseCode', $warehouseCode)
            ->OrderBy('u.checkInDate','DESC')
            ->getQuery()
            ->getResult();
    }


    // /**
    //  * @return CheckIn[] Returns an array of CheckIn objects
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
    public function findOneBySomeField($value): ?CheckIn
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
    public function findAllCheckInQuery(string $query)
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.checkInCode LIKE :query OR u.checkInRefNo LIKE :query')
            ->setParameter('query', '%'.$query.'%')
            ->getQuery()
            ->getResult();
    }
}
