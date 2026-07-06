<?php

namespace App\Controller\Pub;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Mime\Address;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Mailer\MailerInterface;

use App\Service\TenantContext;

class MainController extends AbstractController
{
    #[Route('/', name: 'pub_home')]
    public function home(TenantContext $tenantContext): Response
    {
        return $this->render('pub/main/home.html.twig', [
            'tenant' => $tenantContext->getTenant(),
        ]);
    }

    #[Route('/contato', name: 'pub_contact_post', methods:["POST"])]
    public function contactPost(Request $request, ParameterBagInterface $parameters, MailerInterface $mailer, TenantContext $tenantContext): Response
    {
        try{
            $tenant = $tenantContext->getTenant();
            $data = $request->request->all();
            $email = (new TemplatedEmail())
                ->from(new Address($parameters->get('emailFrom'), $tenant ? $tenant->getName() : 'WAB Curriculos'))
                ->to($parameters->get('emailContactTo'))
                ->subject('Contato do site ' . ($tenant ? ' - ' . $tenant->getName() : ''))
                ->htmlTemplate('email/contact.html.twig')
                ->context([
                    'data' => $data,
                    'tenant' => $tenant,
                ])
            ;

            $mailer->send($email);
        }catch(\Exception $e){
            $this->addFlash('contact_f', 'Houve um erro ao enviar seus dados.');
            return $this->redirectToRoute('pub_home', ['_fragment'=>'contato']);
        }
        $this->addFlash('contact_s', 'Sua mensagem foi enviada.');
        return $this->redirectToRoute('pub_home', ['_fragment'=>'contato']);
    }

    #[Route('/trabalhe-conosco/termos', name: 'pub_terms', methods: ['GET'])]
    public function terms(TenantContext $tenantContext): Response
    {
        return $this->render('pub/main/terms.html.twig', [
            'tenant' => $tenantContext->getTenant(),
        ]);
    }

    #[Route('/trabalhe-conosco/privacidade', name: 'pub_privacy', methods: ['GET'])]
    public function privacy(TenantContext $tenantContext): Response
    {
        return $this->render('pub/main/privacy.html.twig', [
            'tenant' => $tenantContext->getTenant(),
        ]);
    }
}
