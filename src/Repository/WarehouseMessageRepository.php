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


    public function getLastMessage($id)
    {return;
        return  $this->createQueryBuilder('w')
            ->andWhere('w.waId = :id')
            ->setParameter('id', $id)
            ->andWhere('w.created > :hourago')
            ->setParameter('hourago', new \DateTime("now - 1 hour"))
            ->setMaxResults(1)
            ->getQuery()
            ->getResult()
        ;
    }

}
