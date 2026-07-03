<?php

namespace App\Controller\Admin;

use App\Entity\Candidate;
use App\Form\Admin\CandidateFilterType;
use App\Repository\CandidateRepository;
use App\Service\TenantContext;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/candidatos')]
final class CandidateController extends AbstractTenantController
{
    #[Route('', name: 'admin_candidate_index', methods: ['GET'])]
    public function index(Request $request, CandidateRepository $repo, TenantContext $tenantContext): Response
    {
        $this->setTenantContext($tenantContext);
        $this->requireTenant();

        $filterForm = $this->createForm(CandidateFilterType::class, null, [
            'tenant' => $tenantContext->getTenant(),
        ]);

        $filterForm->handleRequest($request);

        $filters = $filterForm->isSubmitted() ? array_filter(
            $filterForm->getData(),
            fn ($v) => $v !== null && $v !== '' && $v !== []
        ) : [];

        $candidates     = $repo->findFiltered($filters);
        $totalAll       = $repo->countAll();
        $activeFilters  = count($filters);

        return $this->render('admin/candidate/index.html.twig', [
            'candidates'    => $candidates,
            'filterForm'    => $filterForm,
            'activeFilters' => $activeFilters,
            'totalAll'      => $totalAll,
        ]);
    }


    #[Route('/{id}', name: 'admin_candidate_show', methods: ['GET'])]
    public function show(Candidate $candidate, TenantContext $tenantContext): Response
    {
        $this->setTenantContext($tenantContext);
        $this->requireTenant();
        $this->denyIfNotTenantOwner($candidate);

        return $this->render('admin/candidate/show.html.twig', [
            'candidate' => $candidate,
        ]);
    }

    #[Route('/{id}/download-resume', name: 'admin_candidate_download_resume', methods: ['GET'])]
    public function downloadResume(Candidate $candidate, string $projectDir, TenantContext $tenantContext): Response
    {
        $this->setTenantContext($tenantContext);
        $this->requireTenant();
        $this->denyIfNotTenantOwner($candidate);

        $filename = $candidate->getResumeFilename();
        if (!$filename) {
            throw new NotFoundHttpException('Este candidato não possui currículo em arquivo.');
        }

        $tenantId = $candidate->getTenant()?->getId();
        $filePath = $projectDir . '/var/uploads/resumes/tenant_' . $tenantId . '/' . $filename;

        if (!file_exists($filePath)) {
            throw new NotFoundHttpException('Arquivo não encontrado no servidor.');
        }

        $candidateName = preg_replace('/[^a-z0-9]/i', '_', $candidate->getName());
        $downloadName  = 'curriculo_' . $candidateName . '.pdf';

        $response = new BinaryFileResponse($filePath);
        $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $downloadName);
        $response->headers->set('Content-Type', 'application/pdf');

        return $response;
    }
}
