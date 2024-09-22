<?php

namespace App\Repository;

use App\Entity\Room;
use App\Entity\Reservation;
use App\Entity\schedule;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\DBAL\Connection;


class RoomRepository extends ServiceEntityRepository
{
    private $connection;

    public function __construct(ManagerRegistry $registry, Connection $connection)
    {
        parent::__construct($registry, Room::class);
        $this->connection = $connection;
    }

    public function findAvailableRooms(\DateTimeInterface $startTime, \DateTimeInterface $endTime): array
    {
        $dayOfWeek = strtolower($startTime->format('l'));
        $startTimeFormatted = $startTime->format('H:i:s');
        $endTimeFormatted = $endTime->format('H:i:s');

        $sql = '
            SELECT r.*
            FROM room r
            JOIN schedule s ON r.id = s.room_id
            LEFT JOIN reservation res ON s.id = res.schedule_id
            WHERE s.open_time <= :startTime
              AND s.close_time >= :endTime
              AND (res.starttime IS NULL OR res.endtime <= :startTime OR res.starttime >= :endTime)
              AND JSON_CONTAINS(s.available_days, :dayOfWeek, \'$\')
        ';

        $stmt = $this->connection->prepare($sql);
        $stmt->bindValue('startTime', $startTimeFormatted);
        $stmt->bindValue('endTime', $endTimeFormatted);
        $stmt->bindValue('dayOfWeek', json_encode($dayOfWeek));
        $resultSet = $stmt->executeQuery();

        $results = $resultSet->fetchAll(\PDO::FETCH_ASSOC);

        return $this->findBy(['id' => array_column($results, 'id')]);
    }

    public function deleteRoomWithSchedulesAndReservations(Room $room): void
    {
        $entityManager = $this->getEntityManager();
        
        // Delete reservations
        $reservations = $entityManager->getRepository(Reservation::class)->findBy(['room' => $room]);
        foreach ($reservations as $reservation) {
            $entityManager->remove($reservation);
        }

        // Delete schedules
        $schedules = $entityManager->getRepository(Schedule::class)->findBy(['room' => $room]);
        foreach ($schedules as $schedule) {
            $entityManager->remove($schedule);
        }

        // Finally, delete the room
        $entityManager->remove($room);
        $entityManager->flush();
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

