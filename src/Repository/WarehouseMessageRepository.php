<?php

namespace App\Repository;

use App\Entity\WarehouseMessage;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method WarehouseMessage|null find($id, $lockMode = null, $lockVersion = null)
 * @method WarehouseMessage|null findOneBy(array $criteria, array $orderBy = null)
 * @method WarehouseMessage[]    findAll()
 * @method WarehouseMessage[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class WarehouseMessageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, WarehouseMessage::class);
    }

    // /**
    //  * @return WarehouseMessage[] Returns an array of WarehouseMessage objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('w')
            ->andWhere('w.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('w.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?WarehouseMessage
    {
        return $this->createQueryBuilder('w')
            ->andWhere('w.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
