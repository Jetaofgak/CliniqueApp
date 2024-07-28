<?php

namespace App\Repository;

use App\Entity\Reservation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Reservation>
 */
class ReservationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Reservation::class);
    }
    public function calculateReservationRate(int $roomId): float
    {
        $conn = $this->getEntityManager()->getConnection();
    
        $sql = '
            SELECT 
                COUNT(r.id) AS totalReservations,
                DATEDIFF(MAX(r.endtime), MIN(r.starttime)) AS totalDays
            FROM reservation r
            JOIN schedule s ON r.schedule_id = s.id
            WHERE s.room_id = :roomId
        ';
        
        $stmt = $conn->prepare($sql);
        $resultSet = $stmt->executeQuery(['roomId' => $roomId]);
        
        $result = $resultSet->fetch(); // Use fetch() instead of fetchAssociative()
        
        $totalReservations = $result['totalReservations'];
        $totalDays = $result['totalDays'] ?: 1; // Avoid division by zero
        
        return ($totalReservations / $totalDays) * 100; // Reservation rate as percentage
    }
    //    /**
    //     * @return Reservation[] Returns an array of Reservation objects
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

    //    public function findOneBySomeField($value): ?Reservation
    //    {
    //        return $this->createQueryBuilder('r')
    //            ->andWhere('r.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
