<?php

namespace App\Repository;

use App\Entity\AreaOfInterest;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<AreaOfInterest>
 */
class AreaOfInterestRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AreaOfInterest::class);
    }
}
