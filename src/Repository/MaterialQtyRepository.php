<?php

namespace App\Repository;

use App\Entity\MaterialQty;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method MaterialQty|null find($id, $lockMode = null, $lockVersion = null)
 * @method MaterialQty|null findOneBy(array $criteria, array $orderBy = null)
 * @method MaterialQty[]    findAll()
 * @method MaterialQty[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MaterialQtyRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, MaterialQty::class);
    }

    //$query=array();
    
    public function sumQtyByWarehouse($warehouseCode, $materialCode)
    {
        return $this->createQueryBuilder('u')
            ->select('u.materialQty')
            ->andWhere('u.materialCode IN (:materialCode)')
            ->andWhere('u.warehouseCode IN (:warehouseCode)')
            ->setParameter('materialCode', $materialCode)
            ->setParameter('warehouseCode', $warehouseCode)
            ->getQuery()
            ->getResult();
    }

    public function findQueryMaterialQtyByMaterialCode($materialCode, $warehouseCode)
    {
        return $this->createQueryBuilder('m')
            ->select('IDENTITY(m.materialCode) AS materialCode, m.materialQty')
            ->where('m.materialCode IN (:materialCode)')
            ->andWhere('m.warehouseCode = :warehouseCode')
            ->setParameter('materialCode', $materialCode)
            ->setParameter('warehouseCode', $warehouseCode)
            ->getQuery()
            ->getResult();
    }

    public function summaryMaterialByWarehouse(string $warehouseCode)
    {
        return $this->createQueryBuilder('u')
            ->select('m.materialCode, m.materialName, u.materialQty,u.warehouseCode')
            ->join('App\Entity\MaterialInfo', 'm', 'WITH', 'u.materialCode = m.materialCode')
            ->Where('u.warehouseCode = :warehouseCode')
            ->setParameter('warehouseCode', $warehouseCode)
            ->getQuery()
            ->getResult();
    }

    public function summaryMaterialAdmin()
    {
        return $this->createQueryBuilder('u')
            ->select('m.materialCode, m.materialName, u.materialQty,u.warehouseCode')
            ->join('App\Entity\MaterialInfo', 'm', 'WITH', 'u.materialCode = m.materialCode')
//            ->Where('u.warehouseCode = :warehouseCode')
//            ->setParameter('warehouseCode', $warehouseCode)
            ->getQuery()
            ->getResult();
    }

    // /**
    //  * @return MaterialQty[] Returns an array of MaterialQty objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('m.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?MaterialQty
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
