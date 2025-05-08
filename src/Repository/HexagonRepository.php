<?php

namespace App\Repository;

use App\Entity\Hexagon;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Hexagon>
 */
class HexagonRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Hexagon::class);
    }

    public function findColorByCoordinates(float $lat, float $lng): ?string
    {
        return $this->createQueryBuilder('h')
            ->select('h.color')
            ->where('h.latitude = :lat')
            ->andWhere('h.longitude = :lng')
            ->setParameters([
                'lat' => $lat,
                'lng' => $lng
            ])
            ->getQuery()
            ->getOneOrNullResult();
    }


    //    /**
    //     * @return Hexagon[] Returns an array of Hexagon objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('h')
    //            ->andWhere('h.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('h.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Hexagon
    //    {
    //        return $this->createQueryBuilder('h')
    //            ->andWhere('h.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
