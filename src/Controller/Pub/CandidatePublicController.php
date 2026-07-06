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
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
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
        MailerInterface $mailer,
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

            try {
                $dashboardUrl = $this->generateUrl('pub_candidate_dashboard', [], UrlGeneratorInterface::ABSOLUTE_URL);
                $welcomeEmail = (new TemplatedEmail())
                    ->from(new Address($this->getParameter('emailFrom'), $tenant->getName()))
                    ->to(new Address($candidate->getEmail(), $candidate->getName()))
                    ->subject('Bem-vindo ao ' . $tenant->getName())
                    ->htmlTemplate('email/candidate_welcome.html.twig')
                    ->context([
                        'candidate'    => $candidate,
                        'tenant'       => $tenant,
                        'dashboardUrl' => $dashboardUrl,
                    ]);
                $mailer->send($welcomeEmail);
            } catch (\Throwable $e) {
                error_log('Mailer Error (Candidate Welcome Email): ' . $e->getMessage());
            }

            $security->login($candidate, 'security.authenticator.form_login.candidate');

            return $this->redirectToRoute('pub_candidate_dashboard');
        }

        return $this->render('pub/candidate/register.html.twig', [
            'form'   => $form,
            'tenant' => $tenant,
        ]);
    }

    #[Route('/minha-conta', name: 'pub_candidate_dashboard', methods: ['GET'])]
    public function dashboard(TenantContext $tenantContext): Response
    {
        return $this->render('pub/candidate/dashboard.html.twig', [
            'candidate' => $this->getUser(),
            'tenant'    => $tenantContext->getTenant(),
        ]);
    }

    #[Route('/esqueci-a-senha', name: 'pub_candidate_forgot_password', methods: ['GET', 'POST'])]
    public function forgotPassword(
        Request $request,
        CandidateRepository $repo,
        EntityManagerInterface $em,
        TenantContext $tenantContext,
        MailerInterface $mailer,
        ParameterBagInterface $params,
    ): Response {
        $tenant = $tenantContext->getTenant();

        if ($request->isMethod('POST')) {
            $email     = $request->request->get('email', '');
            $candidate = $repo->findOneBy(['email' => $email, 'tenant' => $tenant]);

            if ($candidate !== null) {
                $token     = bin2hex(random_bytes(32));
                $expiresAt = new \DateTimeImmutable('+1 hour');
                $candidate->setResetToken($token);
                $candidate->setResetTokenExpiresAt($expiresAt);
                $em->flush();

                $resetUrl = $this->generateUrl(
                    'pub_candidate_reset_password',
                    ['token' => $token],
                    UrlGeneratorInterface::ABSOLUTE_URL
                );

                try {
                    $emailMessage = (new TemplatedEmail())
                        ->from(new Address($params->get('emailFrom'), $tenant ? $tenant->getName() : 'Trabalhe Conosco'))
                        ->to(new Address($candidate->getEmail(), $candidate->getName()))
                        ->subject('Redefinição de senha')
                        ->htmlTemplate('email/candidate_reset_password.html.twig')
                        ->context([
                            'candidate' => $candidate,
                            'tenant'    => $tenant,
                            'resetUrl'  => $resetUrl,
                            'expiresAt' => $expiresAt,
                        ]);
                    $mailer->send($emailMessage);
                } catch (\Throwable $e) {
                    error_log('Mailer Error (Candidate Reset Password): ' . $e->getMessage());
                }
            }

            $this->addFlash('success', 'Se este e-mail estiver cadastrado, você receberá um link de recuperação em breve.');
            return $this->redirectToRoute('pub_candidate_forgot_password');
        }

        return $this->render('pub/candidate/forgot_password.html.twig', [
            'tenant' => $tenant,
        ]);
    }

    #[Route('/redefinir-senha/{token}', name: 'pub_candidate_reset_password', methods: ['GET', 'POST'])]
    public function resetPassword(
        string $token,
        Request $request,
        CandidateRepository $repo,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $hasher,
        TenantContext $tenantContext,
    ): Response {
        $tenant    = $tenantContext->getTenant();
        $candidate = $repo->findOneBy(['resetToken' => $token, 'tenant' => $tenant]);

        if ($candidate === null || $candidate->getResetTokenExpiresAt() < new \DateTimeImmutable()) {
            $this->addFlash('error', 'Link inválido ou expirado. Solicite um novo.');
            return $this->redirectToRoute('pub_candidate_forgot_password');
        }

        if ($request->isMethod('POST')) {
            $password = $request->request->get('password', '');
            $confirm  = $request->request->get('confirm', '');

            if (strlen($password) < 8) {
                $this->addFlash('error', 'A senha deve ter pelo menos 8 caracteres.');
            } elseif ($password !== $confirm) {
                $this->addFlash('error', 'As senhas não coincidem.');
            } else {
                $candidate->setPassword($hasher->hashPassword($candidate, $password));
                $candidate->setResetToken(null);
                $candidate->setResetTokenExpiresAt(null);
                $em->flush();

                $this->addFlash('success', 'Senha redefinida com sucesso! Faça login.');
                return $this->redirectToRoute('pub_candidate_login');
            }
        }

        return $this->render('pub/candidate/reset_password.html.twig', [
            'tenant' => $tenant,
            'token'  => $token,
        ]);
    }
}
