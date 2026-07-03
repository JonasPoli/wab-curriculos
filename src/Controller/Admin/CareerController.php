<?php

namespace App\Controller\Admin;

use App\Entity\Career;
use App\Form\CareerType;
use App\Repository\AreaOfInterestRepository;
use App\Repository\CareerRepository;
use App\Service\TenantContext;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/cargos')]
final class CareerController extends AbstractTenantController
{
    #[Route('', name: 'admin_career_index', methods: ['GET'])]
    public function index(AreaOfInterestRepository $areaRepo, TenantContext $tenantContext): Response
    {
        $this->setTenantContext($tenantContext);
        $this->requireTenant();

        return $this->render('admin/career/index.html.twig', [
            'areas' => $areaRepo->findAll(),
        ]);
    }

    #[Route('/new', name: 'admin_career_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em, AreaOfInterestRepository $areaRepo, TenantContext $tenantContext): Response
    {
        $this->setTenantContext($tenantContext);
        $this->requireTenant();

        $career = new Career();
        $areas  = $areaRepo->findAll();

        $form = $this->createForm(CareerType::class, $career, ['areas' => $areas]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($career);
            $em->flush();
            $this->addFlash('success', 'Cargo criado.');
            return $this->redirectToRoute('admin_career_index');
        }

        return $this->render('admin/career/new.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_career_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        Career $career,
        EntityManagerInterface $em,
        AreaOfInterestRepository $areaRepo,
        TenantContext $tenantContext,
    ): Response {
        $this->setTenantContext($tenantContext);
        $this->requireTenant();
        $this->denyIfNotTenantOwner($career->getArea());

        $areas = $areaRepo->findAll();
        $form  = $this->createForm(CareerType::class, $career, ['areas' => $areas]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Cargo atualizado.');
            return $this->redirectToRoute('admin_career_index');
        }

        return $this->render('admin/career/edit.html.twig', [
            'career' => $career,
            'form'   => $form,
        ]);
    }

    #[Route('/{id}/delete', name: 'admin_career_delete', methods: ['POST'])]
    public function delete(Request $request, Career $career, EntityManagerInterface $em, TenantContext $tenantContext): Response
    {
        $this->setTenantContext($tenantContext);
        $this->requireTenant();
        $this->denyIfNotTenantOwner($career->getArea());

        if ($this->isCsrfTokenValid('delete_career_' . $career->getId(), $request->request->get('_token'))) {
            $em->remove($career);
            $em->flush();
            $this->addFlash('success', 'Cargo excluído.');
        }
        return $this->redirectToRoute('admin_career_index');
    }

    #[Route('/reorder', name: 'admin_career_reorder', methods: ['POST'])]
    public function reorder(Request $request, CareerRepository $repo, EntityManagerInterface $em): JsonResponse
    {
        $ids = $request->request->all('ids');
        foreach ($ids as $position => $id) {
            $career = $repo->find((int)$id);
            if ($career) {
                $career->setPosition((int)$position);
            }
        }
        $em->flush();
        return new JsonResponse(['ok' => true]);
    }
}
