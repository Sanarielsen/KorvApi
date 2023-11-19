<?php

namespace App\Repository;

use App\Entity\Sensor;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Sensor>
 *
 * @method Sensor|null find($id, $lockMode = null, $lockVersion = null)
 * @method Sensor|null findOneBy(array $criteria, array $orderBy = null)
 * @method Sensor[]    findAll()
 * @method Sensor[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SensorRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Sensor::class);
    }

    public function findSensorById(int $id)
    {
        return $this->createQueryBuilder('sensor')
            ->select('sensor.id, sensor.name, sensor.type, sensor.status, sensor.isActivated')
            ->AndWhere('sensor.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getResult();
    }

//    /**
//     * @return Sensor[] Returns an array of Sensor objects
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

//    public function findOneBySomeField($value): ?Sensor
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
