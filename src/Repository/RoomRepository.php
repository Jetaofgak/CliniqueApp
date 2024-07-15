<?php

namespace App\Repository;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Room;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use Psr\Log\LoggerInterface;
use Doctrine\ORM\EntityRepository;




/**
 * @extends ServiceEntityRepository<Room>
 */
class RoomRepository extends EntityRepository
{
    private $logger;
    private $entityManager;

    public function __construct(entityManager $em, LoggerInterface $logger)
    {
        parent::__construct($em);
        $this->logger = $logger;
    }

    public function findAvailableRooms(\DateTimeInterface $startTime, \DateTimeInterface $endTime): array
    {
        $entityManager = $this->getEntityManager();

        $query = $entityManager->createQuery(
            'SELECT r
            FROM App\Entity\Room r
            JOIN r.schedules s
            WHERE s.openTime <= :startTime
            AND s.closeTime >= :endTime
            AND NOT EXISTS (
                SELECT 1
                FROM App\Entity\Reservation re
                WHERE re.schedule = s
                AND re.starttime < :endTime
                AND re.endtime > :startTime
            )'
        )
        ->setParameter('startTime', $startTime)
        ->setParameter('endTime', $endTime);

        // Log the generated SQL query
        $sql = $query->getSQL();
        $this->logger->info('Generated SQL query: '.$sql);

        // Log parameters separately
        foreach ($query->getParameters() as $parameter) {
            $this->logger->info(sprintf(
                'Parameter "%s" = "%s"',
                $parameter->getName(),
                $parameter->getValue()
            ));
        }

        // Execute the query and return the result
        return $query->getResult();
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

