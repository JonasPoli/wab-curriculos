<?php

namespace App\Controller\Security;

use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class ResetPasswordController extends AbstractController
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly EntityManagerInterface $em,
        private readonly UserPasswordHasherInterface $hasher,
        private readonly MailerInterface $mailer,
        private readonly ParameterBagInterface $params,
    ) {}

    // ── Step 1: Formulário de e-mail ──────────────────────────────────────────
    #[Route('/esqueci-a-senha', name: 'app_forgot_password', methods: ['GET', 'POST'])]
    public function request(Request $request): Response
    {
        $error   = null;
        $success = false;

        if ($request->isMethod('POST')) {
            $email = trim($request->request->getString('email'));
            $user  = $this->userRepository->findOneBy(['email' => $email]);

            if ($user) {
                // Gerar token único
                $token     = bin2hex(random_bytes(32));
                $expiresAt = new \DateTime('+1 hour');

                $user->setResetPasswordToken($token);
                $user->setResetPasswordExpiresAt($expiresAt);
                $this->em->flush();

                // Enviar e-mail
                $resetUrl = $this->generateUrl(
                    'app_reset_password',
                    ['token' => $token],
                    UrlGeneratorInterface::ABSOLUTE_URL
                );

                try {
                    $emailMessage = (new TemplatedEmail())
                        ->from(new Address($this->params->get('emailFrom'), 'WAB Admin'))
                        ->to(new Address($user->getEmail(), $user->getDisplayName()))
                        ->subject('Redefinição de senha')
                        ->htmlTemplate('email/reset_password.html.twig')
                        ->context([
                            'user'      => $user,
                            'resetUrl'  => $resetUrl,
                            'expiresAt' => $expiresAt,
                        ])
                    ;
                    $this->mailer->send($emailMessage);
                } catch (\Exception $e) {
                    // Silently fail — security: don't reveal if email exists
                }
            }

            // Always show success (security: don't reveal if e-mail exists)
            $success = true;
        }

        return $this->render('security/forgot_password.html.twig', [
            'error'   => $error,
            'success' => $success,
        ]);
    }

    // ── Step 2: Formulário de nova senha ──────────────────────────────────────
    #[Route('/redefinir-senha/{token}', name: 'app_reset_password', methods: ['GET', 'POST'])]
    public function reset(Request $request, string $token): Response
    {
        $user = $this->userRepository->findOneBy(['resetPasswordToken' => $token]);

        // Token inválido ou expirado
        if (!$user || !$user->isResetPasswordTokenValid()) {
            $this->addFlash('error', 'O link de redefinição é inválido ou expirou. Solicite um novo.');
            return $this->redirectToRoute('app_forgot_password');
        }

        $error = null;

        if ($request->isMethod('POST')) {
            $password = $request->request->getString('password');
            $confirm  = $request->request->getString('password_confirm');

            if (strlen($password) < 8) {
                $error = 'A senha deve ter pelo menos 8 caracteres.';
            } elseif ($password !== $confirm) {
                $error = 'As senhas não conferem.';
            } else {
                $user->setPassword($this->hasher->hashPassword($user, $password));
                $user->setResetPasswordToken(null);
                $user->setResetPasswordExpiresAt(null);
                $this->em->flush();

                $this->addFlash('success', 'Senha redefinida com sucesso! Faça login com a nova senha.');
                return $this->redirectToRoute('app_login');
            }
        }

        return $this->render('security/reset_password.html.twig', [
            'token' => $token,
            'error' => $error,
        ]);
    }
}
