<?php

namespace App\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/icones')]
final class IconesController extends AbstractController
{
    #[Route('', name: 'app_admin_icones_index', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('admin/icones/index.html.twig');
    }
}
