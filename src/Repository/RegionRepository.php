<?php

namespace App\Repository;

use App\Entity\Region;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Region>
 *
 * @method Region|null find($id, $lockMode = null, $lockVersion = null)
 * @method Region|null findOneBy(array $criteria, array $orderBy = null)
 * @method Region[]    findAll()
 * @method Region[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RegionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Region::class);
    }

    /**
     * @return bool Returns if you have exists region informed
     */
    public function findOneByIdExists(int $id): bool
    {
        $regionResult = $this->createQueryBuilder('region')
            ->andWhere('region.id = :id')
            ->setParameter('id', $id)
            ->setMaxResults(1)
            ->getQuery()
            ->getResult();

        return count($regionResult) > 0 && $regionResult[0]->getId() === $id;
    }

    public function deleteCurrentRegion(int $id): bool
    {
        $regionDelete = $this->createQueryBuilder('Region')
            ->delete('Region', 'region')
            ->where('region.id = :id')
            ->setParameter(':id', $id)
            ->getQuery()
            ->getResult();

        var_dump($regionDelete);

        return true;
    }
//    /**
//     * @return Region[] Returns an array of Region objects
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

//    public function findOneBySomeField($value): ?Region
//    {
//        return $this->createQueryBuilder('r')
//            ->andWhere('r.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
