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

    public function findSensorsByLocal(int $idLocal): array
    {
        return $this->createQueryBuilder('sensor')
            ->select('sensor.id, sensor.name, sensor.type, sensor.status, sensor.isActivated')
            ->andWhere('sensor.local = :localId')
            ->setParameter('localId', $idLocal)
            ->getQuery()
            ->getResult();
    }

    public function findSensorByLocalId(int $idLocal): array|null
    {
        return $this->createQueryBuilder('sensor')
            ->select('sensor.id, sensor.name, sensor.type, sensor.status, sensor.isActivated, IDENTITY(sensor.local) as localId')
            ->andWhere('IDENTITY(sensor.local) = :localId')
            ->setParameter('localId', $idLocal)
            ->getQuery()
            ->getResult();
    }

    public function findSensorTypeByLocal(int $idLocal, string $typeSensor): array|null
    {
        return $this->createQueryBuilder('sensor')
            ->select('sensor.id, sensor.name, sensor.type, sensor.status, sensor.isActivated')
            ->andWhere('sensor.local = :localId')
            ->andWhere('sensor.type = :typeSensor')
            ->setParameter('localId', $idLocal)
            ->setParameter('typeSensor', $typeSensor)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findWhichTypeSensorIsCreatedByLocal(int $idLocal): array
    {
        return $this->createQueryBuilder('sensor')
            ->select('sensor.type')
            ->andWhere('sensor.local = :localId')
            ->setParameter('localId', $idLocal)
            ->distinct()
            ->getQuery()
            ->getResult();
    }
}
