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
            $qMode = $filters['qMode'] ?? 'or';
            $qMode = strtolower($qMode) === 'and' ? 'and' : 'or';

            $terms = array_filter(
                array_map('trim', explode(' ', $filters['q'])),
                fn($t) => $t !== ''
            );

            if (!empty($terms)) {
                $termExpressions = [];
                foreach ($terms as $index => $term) {
                    $paramName = 'q_' . $index;
                    $termExpressions[] = $qb->expr()->orX(
                        $qb->expr()->like('c.name', ':' . $paramName),
                        $qb->expr()->like('c.email', ':' . $paramName)
                    );
                    $qb->setParameter($paramName, '%' . $term . '%');
                }

                if ($qMode === 'and') {
                    $qb->andWhere($qb->expr()->andX(...$termExpressions));
                } else {
                    $qb->andWhere($qb->expr()->orX(...$termExpressions));
                }
            }
        }

        if (!empty($filters['area'])) {
            $areaIds = [];
            $rawAreas = is_iterable($filters['area']) ? $filters['area'] : [$filters['area']];
            foreach ($rawAreas as $item) {
                $areaIds[] = $item instanceof \App\Entity\AreaOfInterest ? $item->getId() : (int) $item;
            }
            if (!empty($areaIds)) {
                $qb->andWhere('area.id IN (:areas)')
                   ->setParameter('areas', $areaIds);
            }
        }

        if (!empty($filters['career'])) {
            $careerIds = [];
            $rawCareers = is_iterable($filters['career']) ? $filters['career'] : [$filters['career']];
            foreach ($rawCareers as $item) {
                $careerIds[] = $item instanceof \App\Entity\Career ? $item->getId() : (int) $item;
            }
            if (!empty($careerIds)) {
                $qb->andWhere('career.id IN (:careers)')
                   ->setParameter('careers', $careerIds);
            }
        }

        if (!empty($filters['state'])) {
            $states = is_iterable($filters['state']) ? $filters['state'] : [$filters['state']];
            $qb->andWhere('c.state IN (:states)')
               ->setParameter('states', $states);
        }

        if (!empty($filters['city'])) {
            $qb->andWhere('c.city LIKE :city')
               ->setParameter('city', '%' . $filters['city'] . '%');
        }

        if (!empty($filters['contractType'])) {
            $contractTypes = is_iterable($filters['contractType']) ? $filters['contractType'] : [$filters['contractType']];
            $orStatements = [];
            foreach ($contractTypes as $index => $ct) {
                $normalizedCt = match (strtolower($ct)) {
                    'clt' => 'CLT',
                    'pj' => 'PJ',
                    'estagio', 'estágio' => 'Estágio',
                    'temporario', 'temporário' => 'Temporário',
                    'voluntario', 'voluntário' => 'Voluntário',
                    default => $ct,
                };
                $paramName = 'ct_' . $index;
                $orStatements[] = "c.contractTypes LIKE :" . $paramName;
                $qb->setParameter($paramName, '%"' . $normalizedCt . '"%');
            }
            if (!empty($orStatements)) {
                $qb->andWhere($qb->expr()->orX(...$orStatements));
            }
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
            $qMode = $filters['qMode'] ?? 'or';
            $qMode = strtolower($qMode) === 'and' ? 'and' : 'or';

            $terms = array_filter(
                array_map('trim', explode(' ', $filters['q'])),
                fn($t) => $t !== ''
            );

            if (!empty($terms)) {
                $termExpressions = [];
                foreach ($terms as $index => $term) {
                    $paramName = 'q_' . $index;
                    $termExpressions[] = $qb->expr()->orX(
                        $qb->expr()->like('c.name', ':' . $paramName),
                        $qb->expr()->like('c.email', ':' . $paramName)
                    );
                    $qb->setParameter($paramName, '%' . $term . '%');
                }

                if ($qMode === 'and') {
                    $qb->andWhere($qb->expr()->andX(...$termExpressions));
                } else {
                    $qb->andWhere($qb->expr()->orX(...$termExpressions));
                }
            }
        }
        if (!empty($filters['area'])) {
            $areaIds = [];
            $rawAreas = is_iterable($filters['area']) ? $filters['area'] : [$filters['area']];
            foreach ($rawAreas as $item) {
                $areaIds[] = $item instanceof \App\Entity\AreaOfInterest ? $item->getId() : (int) $item;
            }
            if (!empty($areaIds)) {
                $qb->andWhere('area.id IN (:areas)')
                   ->setParameter('areas', $areaIds);
            }
        }
        if (!empty($filters['career'])) {
            $careerIds = [];
            $rawCareers = is_iterable($filters['career']) ? $filters['career'] : [$filters['career']];
            foreach ($rawCareers as $item) {
                $careerIds[] = $item instanceof \App\Entity\Career ? $item->getId() : (int) $item;
            }
            if (!empty($careerIds)) {
                $qb->andWhere('career.id IN (:careers)')
                   ->setParameter('careers', $careerIds);
            }
        }
        if (!empty($filters['state'])) {
            $states = is_iterable($filters['state']) ? $filters['state'] : [$filters['state']];
            $qb->andWhere('c.state IN (:states)')
               ->setParameter('states', $states);
        }

        return (int) $qb->getQuery()->getSingleScalarResult();
    }
}

