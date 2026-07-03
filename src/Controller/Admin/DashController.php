<?php

namespace App\Controller\Admin;

use App\Repository\AreaOfInterestRepository;
use App\Repository\CandidateRepository;
use App\Repository\CareerRepository;
use App\Repository\TenantRepository;
use App\Service\TenantContext;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin')]
class DashController extends AbstractController
{
    #[Route('/', name: 'admin_dash')]
    public function dashboard(
        TenantContext $tenantContext,
        CandidateRepository $candidateRepository,
        AreaOfInterestRepository $areaRepository,
        CareerRepository $careerRepository,
        TenantRepository $tenantRepository,
    ): Response {
        $tenant = $tenantContext->getTenant();
        $isSuperAdmin = $this->isGranted('ROLE_SUPER_ADMIN') && $tenant === null;

        if ($tenant !== null) {
            $stats = [
                'candidates_total'    => $candidateRepository->count(['tenant' => $tenant]),
                'candidates_new'      => $candidateRepository->countNewThisMonth($tenant),
                'candidates_active'   => $candidateRepository->count(['tenant' => $tenant, 'activeRegistration' => true]),
                'areas_total'         => $areaRepository->count(['tenant' => $tenant]),
                'careers_total'       => $careerRepository->countByTenant($tenant),
                'careers_active'      => $careerRepository->countActiveByTenant($tenant),
                'recent_candidates'   => $candidateRepository->findRecentByTenant($tenant, 5),
            ];
        } else {
            $stats = [
                'tenants_total'    => $tenantRepository->count([]),
                'candidates_total' => $candidateRepository->countAll(),
            ];
        }

        return $this->render('admin/dash/dashboard.html.twig', [
            'stats'        => $stats,
            'tenant'       => $tenant,
            'isSuperAdmin' => $isSuperAdmin,
        ]);
    }
}
