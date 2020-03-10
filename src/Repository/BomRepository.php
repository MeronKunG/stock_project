<?php

namespace App\Repository;

use App\Entity\Bom;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Bom|null find($id, $lockMode = null, $lockVersion = null)
 * @method Bom|null findOneBy(array $criteria, array $orderBy = null)
 * @method Bom[]    findAll()
 * @method Bom[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BomRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Bom::class);
    }

    public function findBomQtyBySku($skuCode)
    {
        return $this->createQueryBuilder('u')
            // ->select('u.quantity')
            ->andWhere('u.skuCode IN (:skuCode)')
            ->setParameter('skuCode', $skuCode)
            ->getQuery()
            ->getResult();
    }

    // public function findSku(string $query)
    // {
    //     return $this->createQueryBuilder('u')
    //         ->select('u.materialCode,u.quantity')
    //         ->join('App\Entity\skuInfo', 's')
    //         ->andWhere('u.skuCode = s.skuCode')

    //         //->setParameter('query', $query)
    //         ->getQuery()
    //         ->getResult();
    // }

    // /**
    //  * @return Bom[] Returns an array of Bom objects
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
    public function findOneBySomeField($value): ?Bom
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    public function findBySkuCode($value)
    {
        return $this->createQueryBuilder('b')
//            ->select('b, m.materialName')
//            ->leftJoin('b.material', 'm')
            ->andWhere('b.skuCode = :val')
            ->setParameter('val', $value)
            ->orderBy('b.id', 'ASC')
            ->getQuery()
            ->getResult()
            ;
    }

}
