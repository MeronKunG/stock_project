<?php

namespace App\Repository;

use App\Entity\PackingBatchInvoice;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Doctrine\ORM\Query;

/**
 * @method PackingBatchInvoice|null find($id, $lockMode = null, $lockVersion = null)
 * @method PackingBatchInvoice|null findOneBy(array $criteria, array $orderBy = null)
 * @method PackingBatchInvoice[]    findAll()
 * @method PackingBatchInvoice[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PackingBatchInvoiceRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, PackingBatchInvoice::class);
    }

    // /**
    //  * @return PackingBatchInvoice[] Returns an array of PackingBatchInvoice objects
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
    public function findOneBySomeField($value): ?PackingBatchInvoice
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    public function findInvoiceCodeByMaterialCode($materialCode, $packingBatchId)
    {
        return $this->createQueryBuilder('pi')
            ->select('pi.invoiceCode, o.orderReceiveAt')
            ->join('App\Entity\OrderInvoiceItem', 'oi', 'WITH', 'pi.invoiceCode = oi.invoice')
            ->join('App\Entity\OrderInvoice', 'o', 'WITH', 'oi.invoice = o.invoice')
            ->join('App\Entity\Bom', 'b', 'WITH', 'oi.skuCode = b.skuCode')
            ->andWhere('b.material IN (:materialCode)')
            ->andWhere('pi.packingBatchId = :packingBatchId')
            ->setParameter('materialCode', $materialCode)
            ->setParameter('packingBatchId', $packingBatchId)
            ->groupBy('pi.invoiceCode')
            ->orderBy('o.orderReceiveAt', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findSkuRequireByPackingBatchID($packingBatchId)
    {
        return $this->createQueryBuilder('pInv')
            ->select('IDENTITY(oi.skuCode) as skuCode,s.skuName,SUM(oi.skuQuantity) as skuQuantity')
            ->join('App\Entity\OrderInvoiceItem', 'oi', 'WITH', 'pInv.subInvoice = oi.subInvoice')
            ->join('App\Entity\SkuInfo', 's', 'WITH', 'oi.skuCode = s.skuCode')
            ->Where('pInv.packingBatchId = :packingBatchId')
            ->setParameter('packingBatchId', $packingBatchId)
            ->groupBy('oi.skuCode,s.skuName')
            ->getQuery()
            ->getResult();
    }

    public function findQueryLabelByPackingBatchId($packingBatchId)
    {
        return $this->createQueryBuilder('b_inv')
            ->select('o_inv.invoice,
              o_inv.orderPhoneNo,
              o_inv.orderName,
              o_inv.orderAddress,
              o_inv.postCode,
              o_inv.tracking,
              o_inv.typeTransport,
              o_inv.codValue,
              o_inv.logisticsSelector, 
              o_inv.orderShortNote,
              o_inv.materialLabel')
            ->leftJoin('App\Entity\OrderInvoice', 'o_inv', 'WITH', 'b_inv.subInvoice = o_inv.subInvoice')
            ->where('o_inv.invoiceStatus = 0')
            ->andWhere('b_inv.packingBatchId = :packingBatchId')

            ->setParameter('packingBatchId', $packingBatchId)
            ->OrderBy('o_inv.primaryMaterialQuantity','ASC')
            ->addOrderBy('o_inv.primaryMaterialCode','ASC')
            ->addOrderBy('o_inv.orderReceiveAt', 'ASC')
            ->addOrderBy('o_inv.invoice', 'ASC')
            ->getQuery()
            ->getResult();
    }
    public function findListLabelByPackingBatchId($packingBatchId)
    {
        return $this->createQueryBuilder('b_inv')
            ->select('o_inv.invoice,
              o_inv.orderPhoneNo,
              o_inv.orderName,
              o_inv.orderAddress,
              o_inv.postCode,
              o_inv.tracking,
              o_inv.typeTransport,
              o_inv.codValue,
              o_inv.logisticsSelector, 
              o_inv.orderShortNote,
              o_inv.materialLabel')
            ->leftJoin('App\Entity\OrderInvoice', 'o_inv', 'WITH', 'b_inv.subInvoice = o_inv.subInvoice')
            ->where('o_inv.invoiceStatus = 0')
            ->andWhere('b_inv.packingBatchId = :packingBatchId')

            ->setParameter('packingBatchId', $packingBatchId)
            ->OrderBy('o_inv.primaryMaterialQuantity','ASC')
            ->addOrderBy('o_inv.primaryMaterialCode','ASC')
            ->addOrderBy('o_inv.orderReceiveAt', 'ASC')
            ->addOrderBy('o_inv.invoice', 'ASC')
            ->getQuery()
            ->getResult();
    }
    public function findInvoiceByPackingBatchId($packingBatchId)
    {
        return $this->createQueryBuilder('p')
            ->Where('p.packingBatchId = :packingBatchId')
            ->setParameter('packingBatchId', $packingBatchId)
            ->getQuery()
            ->getResult();

    }
}
