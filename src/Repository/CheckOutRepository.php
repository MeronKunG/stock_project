<?php

namespace App\Repository;

use App\Entity\CheckOut;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method CheckOut|null find($id, $lockMode = null, $lockVersion = null)
 * @method CheckOut|null findOneBy(array $criteria, array $orderBy = null)
 * @method CheckOut[]    findAll()
 * @method CheckOut[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CheckOutRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, CheckOut::class);
    }

    public function findListCheckOutAdmin()
    {
        return $this->createQueryBuilder('u')
            ->join('App\Entity\WarehouseInfo', 'w', 'WITH', 'u.warehouseCode = w.warehouseCode')
            ->OrderBy('u.checkOutDate','DESC')
            ->getQuery()
            ->getResult();
    }

    public function findListCheckOutUser($warehouseCode)
    {
        return $this->createQueryBuilder('u')
            ->join('App\Entity\WarehouseInfo', 'w', 'WITH', 'u.warehouseCode = w.warehouseCode')
            ->Where('u.warehouseCode = :warehouseCode')
            ->setParameter('warehouseCode', $warehouseCode)
            ->OrderBy('u.checkOutDate','DESC')
            ->getQuery()
            ->getResult();
    }

    public function findAllCheckOutQuery(string $query)
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.checkOutCode LIKE :query OR u.checkOutRefNo LIKE :query')
            ->setParameter('query', '%'.$query.'%')
            ->getQuery()
            ->getResult();
    }

}
