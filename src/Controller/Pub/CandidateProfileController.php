<?php

namespace App\Controller\Pub;

use App\Entity\Candidate;
use App\Entity\WorkExperience;
use App\Entity\AcademicBackground;
use App\Form\Pub\CandidateProfileType;
use App\Form\Pub\CandidateAccessType;
use App\Form\Pub\CandidateAvailabilityType;
use App\Form\Pub\WorkExperiencePublicType;
use App\Form\Pub\AcademicBackgroundPublicType;
use App\Repository\CareerRepository;
use App\Service\TenantContext;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/trabalhe-conosco')]
#[IsGranted('ROLE_CANDIDATE')]
class CandidateProfileController extends AbstractController
{
    #[Route('/perfil', name: 'pub_candidate_profile', methods: ['GET', 'POST'])]
    public function profile(
        Request $request,
        EntityManagerInterface $em,
        TenantContext $tenantContext,
        #[Autowire('%kernel.project_dir%')] string $projectDir,
    ): Response {
        /** @var Candidate $candidate */
        $candidate = $this->getUser();
        $form = $this->createForm(CandidateProfileType::class, $candidate);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $resumeFile = $form->get('resumeFile')->getData();
            if ($resumeFile) {
                $uploadDir = $projectDir . '/var/uploads/resumes';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0775, true);
                }

                $oldFile = $candidate->getResumeFilename();
                if ($oldFile && file_exists($uploadDir . '/' . $oldFile)) {
                    unlink($uploadDir . '/' . $oldFile);
                }

                $newFilename = $candidate->getId() . '_' . uniqid() . '.pdf';
                $resumeFile->move($uploadDir, $newFilename);
                $candidate->setResumeFilename($newFilename);
            }

            $em->flush();
            $this->addFlash('success', 'Dados pessoais atualizados com sucesso.');
            return $this->redirectToRoute('pub_candidate_profile');
        }

        return $this->render('pub/candidate/profile.html.twig', [
            'form'      => $form,
            'candidate' => $candidate,
            'tenant'    => $tenantContext->getTenant(),
        ]);
    }

    #[Route('/curriculo/download', name: 'pub_candidate_resume_download', methods: ['GET'])]
    public function downloadResume(
        #[Autowire('%kernel.project_dir%')] string $projectDir,
    ): Response {
        /** @var Candidate $candidate */
        $candidate = $this->getUser();
        $filename = $candidate->getResumeFilename();

        if (!$filename) {
            throw $this->createNotFoundException();
        }

        $path = $projectDir . '/var/uploads/resumes/' . $filename;
        if (!file_exists($path)) {
            throw $this->createNotFoundException();
        }

        return $this->file($path, 'curriculo_' . $candidate->getId() . '.pdf');
    }

    #[Route('/acesso', name: 'pub_candidate_access', methods: ['GET', 'POST'])]
    public function access(
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $hasher,
        TenantContext $tenantContext,
    ): Response {
        /** @var Candidate $candidate */
        $candidate = $this->getUser();
        $form = $this->createForm(CandidateAccessType::class, $candidate);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plain = $form->get('newPassword')->getData();
            if (!empty($plain)) {
                $candidate->setPassword($hasher->hashPassword($candidate, $plain));
            }
            $em->flush();
            $this->addFlash('success', 'Dados de acesso atualizados.');
            return $this->redirectToRoute('pub_candidate_access');
        }

        return $this->render('pub/candidate/access.html.twig', [
            'form'      => $form,
            'candidate' => $candidate,
            'tenant'    => $tenantContext->getTenant(),
        ]);
    }

    #[Route('/experiencias', name: 'pub_candidate_experience', methods: ['GET', 'POST'])]
    public function experience(Request $request, EntityManagerInterface $em, TenantContext $tenantContext): Response
    {
        /** @var Candidate $candidate */
        $candidate = $this->getUser();
        $we = new WorkExperience();
        $form = $this->createForm(WorkExperiencePublicType::class, $we);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $we->setCandidate($candidate);
            $em->persist($we);
            $em->flush();
            $this->addFlash('success', 'Experiência adicionada.');
            return $this->redirectToRoute('pub_candidate_experience');
        }

        return $this->render('pub/candidate/experience.html.twig', [
            'form'      => $form,
            'candidate' => $candidate,
            'tenant'    => $tenantContext->getTenant(),
        ]);
    }

    #[Route('/experiencias/{id}/remover', name: 'pub_candidate_experience_delete', methods: ['POST'])]
    public function deleteExperience(WorkExperience $we, Request $request, EntityManagerInterface $em): Response
    {
        /** @var Candidate $candidate */
        $candidate = $this->getUser();

        if ($we->getCandidate() !== $candidate) {
            throw $this->createAccessDeniedException();
        }

        if (!$this->isCsrfTokenValid('delete_we_' . $we->getId(), $request->request->get('_token'))) {
            throw $this->createAccessDeniedException();
        }

        $em->remove($we);
        $em->flush();
        $this->addFlash('success', 'Experiência removida.');
        return $this->redirectToRoute('pub_candidate_experience');
    }

    #[Route('/formacao', name: 'pub_candidate_academic', methods: ['GET', 'POST'])]
    public function academic(Request $request, EntityManagerInterface $em, TenantContext $tenantContext): Response
    {
        /** @var Candidate $candidate */
        $candidate = $this->getUser();
        $ab = new AcademicBackground();
        $form = $this->createForm(AcademicBackgroundPublicType::class, $ab);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $ab->setCandidate($candidate);
            $em->persist($ab);
            $em->flush();
            $this->addFlash('success', 'Formação adicionada.');
            return $this->redirectToRoute('pub_candidate_academic');
        }

        return $this->render('pub/candidate/academic.html.twig', [
            'form'      => $form,
            'candidate' => $candidate,
            'tenant'    => $tenantContext->getTenant(),
        ]);
    }

    #[Route('/formacao/{id}/remover', name: 'pub_candidate_academic_delete', methods: ['POST'])]
    public function deleteAcademic(AcademicBackground $ab, Request $request, EntityManagerInterface $em): Response
    {
        /** @var Candidate $candidate */
        $candidate = $this->getUser();

        if ($ab->getCandidate() !== $candidate) {
            throw $this->createAccessDeniedException();
        }

        if (!$this->isCsrfTokenValid('delete_ab_' . $ab->getId(), $request->request->get('_token'))) {
            throw $this->createAccessDeniedException();
        }

        $em->remove($ab);
        $em->flush();
        $this->addFlash('success', 'Formação removida.');
        return $this->redirectToRoute('pub_candidate_academic');
    }

    #[Route('/disponibilidade', name: 'pub_candidate_availability', methods: ['GET', 'POST'])]
    public function availability(
        Request $request,
        EntityManagerInterface $em,
        CareerRepository $careerRepository,
        TenantContext $tenantContext,
    ): Response {
        /** @var Candidate $candidate */
        $candidate = $this->getUser();
        $tenant    = $tenantContext->getTenant();
        $form      = $this->createForm(CandidateAvailabilityType::class, $candidate);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Disponibilidade atualizada.');
            return $this->redirectToRoute('pub_candidate_availability');
        }

        return $this->render('pub/candidate/availability.html.twig', [
            'form'      => $form,
            'candidate' => $candidate,
            'tenant'    => $tenant,
        ]);
    }

    #[Route('/disponibilidade/cargo/{id}/remover', name: 'pub_candidate_career_remove', methods: ['POST'])]
    public function removeCareer(
        \App\Entity\Career $career,
        EntityManagerInterface $em,
    ): Response {
        /** @var Candidate $candidate */
        $candidate = $this->getUser();
        $candidate->removeCareer($career);
        $em->flush();
        return $this->redirectToRoute('pub_candidate_availability');
    }

    #[Route('/excluir-conta', name: 'pub_candidate_delete_account', methods: ['GET', 'POST'])]
    public function deleteAccount(
        Request $request,
        EntityManagerInterface $em,
        Security $security,
        TenantContext $tenantContext,
    ): Response {
        /** @var Candidate $candidate */
        $candidate = $this->getUser();

        if ($request->isMethod('POST')) {
            $word = trim(strtolower((string) $request->request->get('confirm_word')));
            if ($word === 'remover') {
                $security->logout(false);
                $em->remove($candidate);
                $em->flush();
                return $this->redirectToRoute('pub_candidate_account_deleted');
            }
            $this->addFlash('error', 'Digite a palavra "remover" para confirmar.');
        }

        return $this->render('pub/candidate/delete_account.html.twig', [
            'candidate' => $candidate,
            'tenant'    => $tenantContext->getTenant(),
        ]);
    }

    #[Route('/conta-excluida', name: 'pub_candidate_account_deleted', methods: ['GET'])]
    public function accountDeleted(TenantContext $tenantContext): Response
    {
        return $this->render('pub/candidate/account_deleted.html.twig', [
            'tenant' => $tenantContext->getTenant(),
        ]);
    }
}
