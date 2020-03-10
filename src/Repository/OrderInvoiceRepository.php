<?php

namespace App\Repository;

use App\Entity\OrderInvoice;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method OrderInvoice|null find($id, $lockMode = null, $lockVersion = null)
 * @method OrderInvoice|null findOneBy(array $criteria, array $orderBy = null)
 * @method OrderInvoice[]    findAll()
 * @method OrderInvoice[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OrderInvoiceRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, OrderInvoice::class);
    }

    // /**
    //  * @return OrderInvoice[] Returns an array of OrderInvoice objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('o.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    public function findInvoiceNotPacking($warehouseCode){
        return $this->createQueryBuilder('oi')
            ->select('oi.subInvoice, oi.primaryMaterialCode, oi.primaryMaterialQuantity')
            ->leftJoin('App\Entity\PackingBatchInvoice', 'pInv', 'WITH', 'oi.subInvoice = pInv.subInvoice')
            ->Where('oi.invoiceStatus = 0')
            ->andWhere('pInv.subInvoice IS NULL')
            ->andWhere('oi.warehouseCode = :warehouseCode')
            ->setParameter('warehouseCode', $warehouseCode)
            ->orderBy('oi.orderReceiveAt','ASC')
            ->getQuery()
            ->getResult()
            ;
    }

    public function findInvoiceNotPackingByCode($warehouseCode, $code){
        return $this->createQueryBuilder('oi')
            ->select('oi.subInvoice, oi.primaryMaterialQuantity')
            ->leftJoin('App\Entity\PackingBatchInvoice', 'pInv', 'WITH', 'oi.subInvoice = pInv.subInvoice')
            ->Where('oi.invoiceStatus = 0')
            ->andWhere('pInv.subInvoice IS NULL')
            ->andWhere('oi.warehouseCode = :warehouseCode')
            ->andWhere('oi.primaryMaterialCode =:code')
            ->setParameter('code', $code)
            ->setParameter('warehouseCode', $warehouseCode)
            ->orderBy('oi.orderReceiveAt','ASC')
            ->getQuery()
            ->getResult()
            ;
    }

    public function findOrderInvoiceByPackingBatchInvoice($packingBatchId){
        return $this->createQueryBuilder('oi')
            ->join('App\Entity\PackingBatchInvoice', 'pi', 'WITH', 'oi.subInvoice = pi.subInvoice')
            ->where('pi.packingBatchId IN (:packingBatchId)')
            ->andWhere('oi.invoiceStatus = 0')
            ->setParameter('packingBatchId', $packingBatchId)
            ->getQuery()
            ->getResult()
            ;
    }

    public function findInvoiceNotInPackingBatchInvoice($warehouseCode)
    {
        return $this->createQueryBuilder('oi')
            ->select('oi.subInvoice,oi.orderReceiveAt')
            ->leftJoin('App\Entity\PackingBatchInvoice','packInv','WITH','oi.subInvoice=packInv.subInvoice')

            ->where('oi.invoiceStatus = 0')
            ->andWhere('oi.warehouseCode = :warehouseCode')
            ->andWhere('packInv.subInvoice IS NULL')

            ->setParameter('warehouseCode', $warehouseCode)
            ->getQuery()
            ->getResult()
            ;
    }

    public function findInvoicePending($warehouseCode)
    {
        return $this->createQueryBuilder('oi')
//            ->select('oi.subInvoice,oi.orderReceiveAt,oi.orderShortNote')
            ->where('oi.invoiceStatus = 2')
            ->andWhere('oi.warehouseCode = :warehouseCode')
            ->setParameter('warehouseCode', $warehouseCode)
            ->orderBy('oi.orderReceiveAt','ASC')
            ->getQuery()
            ->getResult()
            ;
    }

    public function searchPending(string $query)
    {
        return $this->createQueryBuilder('u')
            ->Where('u.subInvoice LIKE :query')
            ->orWhere('u.orderName LIKE :query')
            ->andWhere('u.invoiceStatus = 2')
            ->setParameter('query', '%'.$query.'%')
            ->getQuery()
            ->getResult();
    }

    /*
    public function findOneBySomeField($value): ?OrderInvoice
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
