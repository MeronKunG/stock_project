<?php

namespace App\Repository;

use App\Entity\OrderInvoiceItem;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method OrderInvoiceItem|null find($id, $lockMode = null, $lockVersion = null)
 * @method OrderInvoiceItem|null findOneBy(array $criteria, array $orderBy = null)
 * @method OrderInvoiceItem[]    findAll()
 * @method OrderInvoiceItem[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OrderInvoiceItemRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, OrderInvoiceItem::class);
    }

    public function findSkuQuantity($invoice, $skuCode)
    {
        return $this->createQueryBuilder('u')
            ->select('u.skuQuantity')
            ->andWhere('u.invoice IN (:invoice)')
            ->andWhere('u.skuCode IN (:skuCode)')
            ->setParameter('invoice', $invoice)
            ->setParameter('skuCode', $skuCode)
            ->addGroupBy('u.invoice')
            ->getQuery()
            ->getResult();
    }
    public function findSkuTest($invoice)
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.invoice IN (:invoice)')
            ->setParameter('invoice', $invoice)
            ->addGroupBy('u.invoice')
            ->getQuery()
            ->getResult();
    }


    public function findSkuCodeByInvoice($packingBatchId)
    {
        return $this->createQueryBuilder('u')
            ->select('u.skuCode')
            ->andWhere('u.invoice = :invoice')
            ->setParameter('invoice', $invoice)
            // ->setParameter('skuCode', $skuCode)
             ->addGroupBy('u.invoice')
            ->getQuery()
            ->getResult();
    }

    public function findSummarySkuByInvoiceAndPackingBatchId($subInvoice, $packingBatchId)
    {
        return $this->createQueryBuilder('o')
            ->select('s.skuCode, sku.skuName, SUM(o.skuQuantity) AS skuQty')
            ->join('o.skuCode', 's')
            ->join('o.invoice', 'c')
            ->join('App\Entity\skuInfo', 'sku', 'WITH', 'sku.skuCode = s.skuCode')
            ->join('App\Entity\PackingBatchInvoice', 'i', 'WITH', 'c.subInvoice = i.subInvoice')
            ->andWhere('c.subInvoice IN (:subInvoice)')
            ->andWhere('i.packingBatchId = :packingBatchId')
            ->setParameter('subInvoice', $subInvoice)
            ->setParameter('packingBatchId', $packingBatchId)
            ->groupBy('s.skuCode, sku.skuName')
            ->orderBy('s.skuCode')
            ->getQuery()
            ->getResult()
            ;
    }

    public function findSummaryMaterialQtyByListInvoiceAndWarehouseCode($invoiceCode, $warehouseCode)
    {
        return $this->createQueryBuilder('o')
            ->select('i.invoice')
            ->join('o.skuCode', 's')
            ->join('o.invoice', 'c')
            ->join('App\Entity\Bom', 'b', 'WITH', 'o.skuCode = b.skuCode')
            ->join('App\Entity\MaterialQty', 'm_qty', 'WITH', 'b.material = m_qty.materialCode')
            ->join('App\Entity\OrderInvoice', 'i', 'WITH', 'c.invoice = i.invoice')
            ->where('m_qty.warehouseCode = :warehouseCode')
            ->andWhere('i.invoice IN (:invoiceCode)')
            ->setParameter('warehouseCode', $warehouseCode)
            ->setParameter('invoiceCode', $invoiceCode)
            ->groupBy('i.invoice')
            ->getQuery()
            ->getResult()
            ;
    }

    public function findBomQty($subInvoice)
    {
        return $this->createQueryBuilder('u')
            ->select('u.skuQuantity,m.materialCode,m.materialName, bom.quantity as bomQty')
            ->join('u.skuCode', 's')
            ->join('App\Entity\Bom', 'bom', 'WITH', 's.skuCode = bom.skuCode')
            ->join('App\Entity\MaterialInfo', 'm', 'WITH', 'bom.material = m.materialCode')
            ->andWhere('u.subInvoice = :subInvoice')
            ->setParameter('subInvoice', $subInvoice)
            ->getQuery()
            ->getResult();
    }


    // /**
    //  * @return OrderInvoiceItem[] Returns an array of OrderInvoiceItem objects
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

    /*
    public function findOneBySomeField($value): ?OrderInvoiceItem
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
