<?php

namespace App\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/exemplo')]
final class ExemploController extends AbstractController
{
    #[Route('', name: 'app_admin_exemplo_index', methods: ['GET'])]
    public function index(): Response
    {
        // Dados fake para demonstração de DataTable
        $records = $this->generateFakeData();

        return $this->render('admin/exemplo/index.html.twig', [
            'records' => $records,
        ]);
    }

    #[Route('/form', name: 'app_admin_exemplo_form', methods: ['GET', 'POST'])]
    public function form(): Response
    {
        return $this->render('admin/exemplo/form.html.twig');
    }

    #[Route('/list', name: 'app_admin_exemplo_list', methods: ['GET', 'POST'])]
    public function list(): Response
    {
        return $this->render('admin/exemplo/list.html.twig');
    }


    // ── Fake data generator ───────────────────────────────────────────────────

    private function generateFakeData(): array
    {
        $names = [
            'Ana Souza', 'Bruno Lima', 'Carla Mendes', 'Diego Rocha', 'Elena Costa',
            'Felipe Alves', 'Gabriela Nunes', 'Henrique Dias', 'Isabela Martins', 'João Pereira',
            'Kátia Oliveira', 'Lucas Santos', 'Mariana Ferreira', 'Nelson Gomes', 'Olívia Ribeiro',
            'Paulo Carvalho', 'Quésia Barbosa', 'Rafael Torres', 'Sandra Pinto', 'Thiago Melo',
        ];

        $statuses = ['Ativo', 'Inativo', 'Pendente'];
        $roles    = ['Administrador', 'Usuário', 'Editor', 'Visualizador'];
        $cities   = ['São Paulo', 'Rio de Janeiro', 'Belo Horizonte', 'Curitiba', 'Porto Alegre', 'Salvador', 'Fortaleza'];

        $records = [];
        foreach ($names as $i => $name) {
            $records[] = [
                'id'       => $i + 1,
                'name'     => $name,
                'email'    => strtolower(str_replace([' ', 'ã', 'é', 'í', 'ó', 'ú', 'â', 'ê', 'ô', 'ç'], ['.', 'a', 'e', 'i', 'o', 'u', 'a', 'e', 'o', 'c'], $name)) . '@exemplo.com',
                'role'     => $roles[array_rand($roles)],
                'status'   => $statuses[array_rand($statuses)],
                'city'     => $cities[array_rand($cities)],
                'balance'  => mt_rand(100, 99999) + (mt_rand(0, 99) / 100),
                'joinedAt' => new \DateTime('-' . mt_rand(10, 1000) . ' days'),
            ];
        }

        return $records;
    }
}
