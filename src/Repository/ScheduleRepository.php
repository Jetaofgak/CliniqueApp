<?php

namespace App\Repository;

use App\Entity\Schedule;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Repository\ReservationRepository;
use DateTimeInterface;

/**
 * @extends ServiceEntityRepository<Schedule>
 */
class ScheduleRepository extends ServiceEntityRepository
{
    private $reservationRepository;

    public function __construct(ManagerRegistry $registry, ReservationRepository $reservationRepository)
    {
        parent::__construct($registry, Schedule::class);
        $this->reservationRepository = $reservationRepository; // Inject the ReservationRepository
    }

    /**
     * Finds available schedules based on time and days.
     */
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

    /**
     * Deletes all schedules associated with a room by room ID and cascades to delete reservations.
     * 
     * @param int $roomId
     */
    public function deleteByRoomId(int $roomId)
    {
        // Find all schedules associated with the room
        $schedules = $this->findBy(['room' => $roomId]);

        // Delete all related reservations for each schedule
        foreach ($schedules as $schedule) {
            $this->reservationRepository->deleteByScheduleId($schedule->getId());
        }

        // Delete the schedules themselves
        $entityManager = $this->getEntityManager();
        foreach ($schedules as $schedule) {
            $entityManager->remove($schedule);
        }
        $entityManager->flush();
    }
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

