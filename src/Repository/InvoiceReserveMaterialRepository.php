<?php

namespace App\Repository;

use App\Entity\InvoiceReserveMaterial;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method InvoiceReserveMaterial|null find($id, $lockMode = null, $lockVersion = null)
 * @method InvoiceReserveMaterial|null findOneBy(array $criteria, array $orderBy = null)
 * @method InvoiceReserveMaterial[]    findAll()
 * @method InvoiceReserveMaterial[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class InvoiceReserveMaterialRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, InvoiceReserveMaterial::class);
    }

    public function sumMaterialQtyReserve(string $materialCode, string $invoice)
    {
        return $this->createQueryBuilder('u')
            ->select('u.materialCode,m.materialName, sum(u.materialQty) as qty')
            ->join('App\Entity\MaterialInfo', 'm', 'WITH', 'u.materialCode = m.materialCode')
            ->andWhere('u.materialCode = :materialCode')
            ->andWhere('u.invoice = :invoice')
            // ->andWhere('u.skuCode = :skuCode')
            ->setParameter('materialCode', $materialCode)
            ->setParameter('invoice', $invoice)
            // ->setParameter('skuCode', $skuCode)
            ->addGroupBy('u.materialCode,m.materialName')
            ->getQuery()
            ->getResult();
    }

    public function findMaterialNameReserve(string $invoice)
    {
        return $this->createQueryBuilder('u')
            ->select('u.materialCode,m.materialName, sum(u.materialQty) as qty')
            ->join('App\Entity\MaterialInfo', 'm', 'WITH', 'u.materialCode = m.materialCode')
            ->andWhere('u.invoice = :invoice')
            ->setParameter('invoice', $invoice)
            ->addGroupBy('u.materialCode,m.materialName')
            ->getQuery()
            ->getResult();
    }

    public function findMaterialReserveByInvoice($warehouseCode, $invoice)
    {
        return $this->createQueryBuilder('u')
            ->select('u.invoice,u.materialCode, u.materialQty')
//            ->join('App\Entity\MaterialInfo', 'm', 'WITH', 'u.materialCode = m.materialCode')
            ->Where('u.invoice = :invoice')
            ->andWhere('u.warehouseCode = :warehouseCode')
            ->setParameter('invoice', $invoice)
            ->setParameter('warehouseCode', $warehouseCode)
            ->getQuery()
            ->getResult();
    }

    // /**
    //  * @return InvoiceReserveMaterial[] Returns an array of InvoiceReserveMaterial objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('i.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?InvoiceReserveMaterial
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
