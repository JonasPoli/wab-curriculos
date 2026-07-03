<?php

namespace App\Controller\Pub;

use App\Entity\Candidate;
use App\Form\Pub\CandidateRegisterType;
use App\Repository\CandidateRepository;
use App\Service\TenantContext;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

#[Route('/trabalhe-conosco')]
class CandidatePublicController extends AbstractController
{
    #[Route('/entrar', name: 'pub_candidate_login', methods: ['GET', 'POST'])]
    public function login(AuthenticationUtils $authenticationUtils, TenantContext $tenantContext): Response
    {
        if ($this->getUser()?->getUserIdentifier()) {
            return $this->redirectToRoute('pub_candidate_dashboard');
        }

        $tenant = $tenantContext->getTenant();
        if ($tenant === null) {
            throw $this->createNotFoundException('Tenant não encontrado para este domínio.');
        }

        return $this->render('pub/candidate/login.html.twig', [
            'last_username' => $authenticationUtils->getLastUsername(),
            'error'         => $authenticationUtils->getLastAuthenticationError(),
            'tenant'        => $tenant,
        ]);
    }

    #[Route('/logout', name: 'pub_candidate_logout', methods: ['GET'])]
    public function logout(): never
    {
        throw new \LogicException('Interceptado pelo firewall.');
    }

    #[Route('/cadastro', name: 'pub_candidate_register', methods: ['GET', 'POST'])]
    public function register(
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $hasher,
        Security $security,
        TenantContext $tenantContext,
        CandidateRepository $candidateRepository,
    ): Response {
        $tenant = $tenantContext->getTenant();
        if ($tenant === null) {
            throw $this->createNotFoundException('Tenant não encontrado.');
        }

        $candidate = new Candidate();
        $candidate->setTenant($tenant);

        $form = $this->createForm(CandidateRegisterType::class, $candidate);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $existing = $candidateRepository->findOneBy([
                'email'  => $candidate->getEmail(),
                'tenant' => $tenant,
            ]);

            if ($existing !== null) {
                $this->addFlash('error', 'Este e-mail já está cadastrado. Faça login ou recupere sua senha.');
                return $this->render('pub/candidate/register.html.twig', [
                    'form'   => $form,
                    'tenant' => $tenant,
                ]);
            }

            $plainPassword = $form->get('plainPassword')->getData();
            $candidate->setPassword($hasher->hashPassword($candidate, $plainPassword));
            $candidate->setActiveRegistration(true);
            $candidate->setLgpdConsent(true);
            $candidate->setLgpdConsentAt(new \DateTime());
            $candidate->setLgpdConsentIp($request->getClientIp());
            $candidate->setLgpdConsentUserAgent($request->headers->get('User-Agent'));

            $em->persist($candidate);
            $em->flush();

            $security->login($candidate, 'security.authenticator.form_login.candidate');

            return $this->redirectToRoute('pub_candidate_dashboard');
        }

        return $this->render('pub/candidate/register.html.twig', [
            'form'   => $form,
            'tenant' => $tenant,
        ]);
    }

    #[Route('/minha-conta', name: 'pub_candidate_dashboard', methods: ['GET'])]
    public function dashboard(TenantContext $tenantContext, CandidateRepository $repo): Response
    {
        /** @var Candidate $candidate */
        $candidate = $this->getUser();
        $tenant    = $tenantContext->getTenant();

        return $this->render('pub/candidate/dashboard.html.twig', [
            'candidate' => $candidate,
            'tenant'    => $tenant,
        ]);
    }
}
