<?php

namespace App\Repository;

use App\Entity\AcademicBackground;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<AcademicBackground>
 */
class AcademicBackgroundRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AcademicBackground::class);
    }
}
