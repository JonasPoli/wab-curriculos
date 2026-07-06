<?php

namespace App\Controller\Admin;

use App\Entity\Candidate;
use App\Entity\SavedSearch;
use App\Entity\User;
use App\Form\Admin\CandidateFilterType;
use App\Repository\CandidateRepository;
use App\Repository\SavedSearchRepository;
use App\Service\TenantContext;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/candidatos')]
final class CandidateController extends AbstractTenantController
{
    #[Route('', name: 'admin_candidate_index', methods: ['GET'])]
    public function index(
        Request $request,
        CandidateRepository $repo,
        TenantContext $tenantContext,
        SavedSearchRepository $savedSearchRepo,
    ): Response {
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

        /** @var User $user */
        $user = $this->getUser();
        $savedSearches = $savedSearchRepo->findByTenantAndUser($tenantContext->getTenant(), $user);

        return $this->render('admin/candidate/index.html.twig', [
            'candidates'     => $candidates,
            'filterForm'     => $filterForm,
            'activeFilters'  => $activeFilters,
            'totalAll'       => $totalAll,
            'savedSearches'  => $savedSearches,
            'currentFilters' => $filters,
        ]);
    }

    #[Route('/salvar-pesquisa', name: 'admin_candidate_save_search', methods: ['POST'])]
    public function saveSearch(
        Request $request,
        EntityManagerInterface $em,
        TenantContext $tenantContext,
    ): Response {
        $this->setTenantContext($tenantContext);
        $this->requireTenant();

        $name    = trim($request->request->getString('search_name'));
        $filters = $request->request->all('filters');

        if ($name === '') {
            $this->addFlash('error', 'Informe um nome para a pesquisa.');
            return $this->redirectToRoute('admin_candidate_index');
        }

        /** @var User $user */
        $user = $this->getUser();

        $savedSearch = new SavedSearch();
        $savedSearch->setTenant($tenantContext->getTenant());
        $savedSearch->setUser($user);
        $savedSearch->setName($name);
        $savedSearch->setFilters($filters);

        $em->persist($savedSearch);
        $em->flush();

        $this->addFlash('success', 'Pesquisa "' . $name . '" salva com sucesso.');
        return $this->redirectToRoute('admin_candidate_index');
    }

    #[Route('/excluir-pesquisa/{id}', name: 'admin_candidate_delete_search', methods: ['POST'])]
    public function deleteSearch(
        SavedSearch $savedSearch,
        EntityManagerInterface $em,
        TenantContext $tenantContext,
    ): Response {
        $this->setTenantContext($tenantContext);
        $this->requireTenant();

        $em->remove($savedSearch);
        $em->flush();

        $this->addFlash('success', 'Pesquisa removida.');
        return $this->redirectToRoute('admin_candidate_index');
    }

    #[Route('/exportar-csv', name: 'admin_candidate_export_csv', methods: ['GET'])]
    public function exportCsv(
        Request $request,
        CandidateRepository $repo,
        TenantContext $tenantContext,
    ): StreamedResponse {
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

        $candidates = $repo->findFiltered($filters);

        $response = new StreamedResponse(function () use ($candidates) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($handle, [
                'ID', 'Nome', 'E-mail', 'Telefone', 'Cidade', 'UF',
                'Cargos de Interesse', 'Tipo de Contrato', 'Início Imediato',
                'Currículo PDF', 'Data de Cadastro',
            ], ';');

            foreach ($candidates as $c) {
                $careers = [];
                foreach ($c->getCareers() as $career) {
                    $careers[] = $career->getTitle();
                }

                fputcsv($handle, [
                    $c->getId(),
                    $c->getName(),
                    $c->getEmail(),
                    $c->getPhone(),
                    $c->getCity(),
                    $c->getState(),
                    implode(', ', $careers),
                    $c->getContractTypes() ? implode(', ', $c->getContractTypes()) : '',
                    $c->isImmediateStart() ? 'Sim' : 'Não',
                    $c->getResumeFilename() ? 'Sim' : 'Não',
                    $c->getCreatedAt()?->format('d/m/Y H:i'),
                ], ';');
            }

            fclose($handle);
        });

        $filename = 'candidatos_' . date('Y-m-d_His') . '.csv';
        $response->headers->set('Content-Type', 'text/csv; charset=UTF-8');
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $filename . '"');

        return $response;
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
