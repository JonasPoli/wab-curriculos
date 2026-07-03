<?php

namespace App\Repository;

use App\Entity\Candidate;
use App\Entity\Tenant;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Candidate>
 */
class CandidateRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Candidate::class);
    }

    public function countByTenant(int $tenantId): int
    {
        return (int) $this->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->where('c.tenant = :tenant')
            ->setParameter('tenant', $tenantId)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function countThisMonthByTenant(int $tenantId): int
    {
        $firstDay = new \DateTimeImmutable('first day of this month midnight');
        return (int) $this->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->where('c.tenant = :tenant')
            ->andWhere('c.createdAt >= :firstDay')
            ->setParameter('tenant', $tenantId)
            ->setParameter('firstDay', $firstDay)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function countNewThisMonth(Tenant $tenant): int
    {
        $firstDay = new \DateTimeImmutable('first day of this month midnight');
        return (int) $this->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->where('c.tenant = :tenant')
            ->andWhere('c.createdAt >= :firstDay')
            ->setParameter('tenant', $tenant)
            ->setParameter('firstDay', $firstDay)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function countAll(): int
    {
        return (int) $this->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function findRecentByTenant(Tenant $tenant, int $limit = 5): array
    {
        return $this->createQueryBuilder('c')
            ->where('c.tenant = :tenant')
            ->setParameter('tenant', $tenant)
            ->orderBy('c.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function findFiltered(array $filters): array
    {
        $qb = $this->createQueryBuilder('c')
            ->leftJoin('c.careers', 'career')
            ->leftJoin('career.area', 'area')
            ->orderBy('c.createdAt', 'DESC');

        if (!empty($filters['q'])) {
            $qb->andWhere('c.name LIKE :q OR c.email LIKE :q')
               ->setParameter('q', '%' . $filters['q'] . '%');
        }

        if (!empty($filters['area'])) {
            $qb->andWhere('area.id = :area')
               ->setParameter('area', (int) $filters['area']);
        }

        if (!empty($filters['career'])) {
            $qb->andWhere('career.id = :career')
               ->setParameter('career', (int) $filters['career']);
        }

        if (!empty($filters['state'])) {
            $qb->andWhere('c.state = :state')
               ->setParameter('state', $filters['state']);
        }

        if (!empty($filters['city'])) {
            $qb->andWhere('c.city LIKE :city')
               ->setParameter('city', '%' . $filters['city'] . '%');
        }

        if (!empty($filters['contractType'])) {
            $qb->andWhere("JSON_CONTAINS(c.contractTypes, :ct) = 1")
               ->setParameter('ct', json_encode($filters['contractType']));
        }

        if (isset($filters['immediateStart']) && $filters['immediateStart'] !== '') {
            $qb->andWhere('c.immediateStart = :is')
               ->setParameter('is', (bool) $filters['immediateStart']);
        }

        if (isset($filters['hasResume']) && $filters['hasResume'] !== '') {
            if ($filters['hasResume']) {
                $qb->andWhere('c.resumeFilename IS NOT NULL AND c.resumeFilename != \'\'');
            } else {
                $qb->andWhere('c.resumeFilename IS NULL OR c.resumeFilename = \'\'');
            }
        }

        if (!empty($filters['dateFrom'])) {
            $qb->andWhere('c.createdAt >= :dateFrom')
               ->setParameter('dateFrom', new \DateTimeImmutable($filters['dateFrom'] . ' 00:00:00'));
        }

        if (!empty($filters['dateTo'])) {
            $qb->andWhere('c.createdAt <= :dateTo')
               ->setParameter('dateTo', new \DateTimeImmutable($filters['dateTo'] . ' 23:59:59'));
        }

        return $qb->getQuery()->getResult();
    }

    public function countFiltered(array $filters): int
    {
        $qb = $this->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->leftJoin('c.careers', 'career')
            ->leftJoin('career.area', 'area');

        if (!empty($filters['q'])) {
            $qb->andWhere('c.name LIKE :q OR c.email LIKE :q')
               ->setParameter('q', '%' . $filters['q'] . '%');
        }
        if (!empty($filters['area'])) {
            $qb->andWhere('area.id = :area')->setParameter('area', (int) $filters['area']);
        }
        if (!empty($filters['career'])) {
            $qb->andWhere('career.id = :career')->setParameter('career', (int) $filters['career']);
        }
        if (!empty($filters['state'])) {
            $qb->andWhere('c.state = :state')->setParameter('state', $filters['state']);
        }

        return (int) $qb->getQuery()->getSingleScalarResult();
    }
}

