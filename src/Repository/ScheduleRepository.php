<?php

namespace App\Repository;

use App\Entity\Schedule;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use DateTimeInterface;

/**
 * @extends ServiceEntityRepository<Schedule>
 */
class ScheduleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Schedule::class);
    }
    public function findAvailableSchedules(DateTimeInterface $startTime, DateTimeInterface $endTime, array $days): array
{
    $qb = $this->createQueryBuilder('s')
        ->innerJoin('s.room', 'r')
        ->where('s.openTime <= :endTime')
        ->andWhere('s.closeTime >= :startTime')
        ->andWhere('s.availableDays IN (:days)')
        ->setParameter('startTime', $startTime)
        ->setParameter('endTime', $endTime)
        ->setParameter('days', $days)
        ->getQuery();

    return $qb->getResult();
}

    //    /**
    //     * @return Schedule[] Returns an array of Schedule objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('s')
    //            ->andWhere('s.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('s.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Schedule
    //    {
    //        return $this->createQueryBuilder('s')
    //            ->andWhere('s.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
