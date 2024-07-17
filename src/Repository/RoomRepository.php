<?php

namespace App\Repository;

use App\Entity\Room;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Query\Expr\Join;

class RoomRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Room::class);
    }

    public function findAvailableRooms(\DateTime $startTime, \DateTime $endTime): array
    {
        return $this->createQueryBuilder('r')
            ->leftJoin('r.schedules', 's', Join::WITH, 's.room = r.id')
            ->andWhere('s.openTime <= :startTime AND s.closeTime >= :endTime')
            ->setParameter('startTime', $startTime)
            ->setParameter('endTime', $endTime)
            ->getQuery()
            ->getResult();
    }
}

    //    /**
    //     * @return Room[] Returns an array of Room objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('r')
    //            ->andWhere('r.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('r.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Room
    //    {
    //        return $this->createQueryBuilder('r')
    //            ->andWhere('r.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }

