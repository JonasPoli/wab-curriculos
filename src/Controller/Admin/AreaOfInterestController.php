<?php

namespace App\Controller\Admin;

use App\Entity\AreaOfInterest;
use App\Form\AreaOfInterestType;
use App\Repository\AreaOfInterestRepository;
use App\Service\TenantContext;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/areas-de-interesse')]
final class AreaOfInterestController extends AbstractTenantController
{
    #[Route('', name: 'admin_area_of_interest_index', methods: ['GET'])]
    public function index(AreaOfInterestRepository $repo, TenantContext $tenantContext): Response
    {
        $this->setTenantContext($tenantContext);
        $this->requireTenant();

        return $this->render('admin/area_of_interest/index.html.twig', [
            'areas' => $repo->findAll(),
        ]);
    }

    #[Route('/new', name: 'admin_area_of_interest_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em, TenantContext $tenantContext): Response
    {
        $this->setTenantContext($tenantContext);
        $tenant = $this->requireTenant();

        $area = new AreaOfInterest();
        $area->setTenant($tenant);

        $form = $this->createForm(AreaOfInterestType::class, $area);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($area);
            $em->flush();
            $this->addFlash('success', 'Área de interesse criada.');
            return $this->redirectToRoute('admin_area_of_interest_index');
        }

        return $this->render('admin/area_of_interest/new.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_area_of_interest_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, AreaOfInterest $area, EntityManagerInterface $em, TenantContext $tenantContext): Response
    {
        $this->setTenantContext($tenantContext);
        $this->requireTenant();
        $this->denyIfNotTenantOwner($area);

        $form = $this->createForm(AreaOfInterestType::class, $area);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Área de interesse atualizada.');
            return $this->redirectToRoute('admin_area_of_interest_index');
        }

        return $this->render('admin/area_of_interest/edit.html.twig', [
            'area' => $area,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/delete', name: 'admin_area_of_interest_delete', methods: ['POST'])]
    public function delete(Request $request, AreaOfInterest $area, EntityManagerInterface $em, TenantContext $tenantContext): Response
    {
        $this->setTenantContext($tenantContext);
        $this->requireTenant();
        $this->denyIfNotTenantOwner($area);

        if ($this->isCsrfTokenValid('delete_area_' . $area->getId(), $request->request->get('_token'))) {
            $em->remove($area);
            $em->flush();
            $this->addFlash('success', 'Área de interesse excluída.');
        }
        return $this->redirectToRoute('admin_area_of_interest_index');
    }

    #[Route('/reorder', name: 'admin_area_of_interest_reorder', methods: ['POST'])]
    public function reorder(Request $request, AreaOfInterestRepository $repo, EntityManagerInterface $em): JsonResponse
    {
        $ids = $request->request->all('ids');
        foreach ($ids as $position => $id) {
            $area = $repo->find((int)$id);
            if ($area) {
                $area->setPosition((int)$position);
            }
        }
        $em->flush();
        return new JsonResponse(['ok' => true]);
    }
}
