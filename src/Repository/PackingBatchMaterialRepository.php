<?php

namespace App\Repository;

use App\Entity\PackingBatchMaterial;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method PackingBatchMaterial|null find($id, $lockMode = null, $lockVersion = null)
 * @method PackingBatchMaterial|null findOneBy(array $criteria, array $orderBy = null)
 * @method PackingBatchMaterial[]    findAll()
 * @method PackingBatchMaterial[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PackingBatchMaterialRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, PackingBatchMaterial::class);
    }

    // /**
    //  * @return BatchMaterial[] Returns an array of BatchMaterial objects
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
    public function findOneBySomeField($value): ?BatchMaterial
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
    public function findQueryBillingByPackingBatchId($packingBatchId)
    {
        return $this->createQueryBuilder('b_mat')
            ->select('o_mat.materialCode, o_mat.materialName, b_mat.materialQty')
            ->leftJoin('App\Entity\MaterialInfo', 'o_mat', 'WITH', 'o_mat.materialCode = b_mat.materialCode')
            ->andWhere('b_mat.packingBatchId = :packingBatchId')
            ->setParameter('packingBatchId', $packingBatchId)
            ->getQuery()
            ->getResult();
    }
}
