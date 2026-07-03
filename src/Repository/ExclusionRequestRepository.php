<?php

namespace App\Repository;

use App\Entity\ExclusionRequest;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ExclusionRequest>
 */
class ExclusionRequestRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ExclusionRequest::class);
    }

    public function findByToken(string $token): ?ExclusionRequest
    {
        return $this->findOneBy(['token' => $token]);
    }
}
