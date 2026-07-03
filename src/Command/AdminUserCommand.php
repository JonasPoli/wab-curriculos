<?php

namespace App\Command;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Validation;

#[AsCommand(
    name: 'app:admin-user',
    description: 'Criar ou listar usuários administradores',
)]
class AdminUserCommand extends Command
{
    public function __construct(
        private readonly UserPasswordHasherInterface $userPasswordHasher,
        private readonly EntityManagerInterface $em,
        private readonly UserRepository $userRepository,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('list',     'l', InputOption::VALUE_NONE, 'Apenas listar os usuários existentes')
            ->addArgument('username', InputArgument::OPTIONAL, 'Username do usuário')
            ->addArgument('email',    InputArgument::OPTIONAL, 'E-mail do usuário')
            ->addArgument('password', InputArgument::OPTIONAL, 'Senha do usuário')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $validator = Validation::createValidator();

        // ── Listar usuários existentes ────────────────────────────────────────
        $this->listUsers($io);

        // Modo --list: sai após listar
        if ($input->getOption('list')) {
            return Command::SUCCESS;
        }

        // ── Username ──────────────────────────────────────────────────────────
        $username = $input->getArgument('username');
        if (!$username) {
            $username = $io->ask(
                'Username',
                'admin',
                function (string $value): string {
                    if (trim($value) === '') {
                        throw new \RuntimeException('O username não pode ser vazio.');
                    }
                    if (strlen($value) < 3) {
                        throw new \RuntimeException('O username deve ter pelo menos 3 caracteres.');
                    }
                    return $value;
                }
            );
        }

        // Verificar se username já existe
        if ($this->userRepository->findOneBy(['username' => $username])) {
            $io->error("Já existe um usuário com o username \"$username\".");
            return Command::FAILURE;
        }

        // ── E-mail ────────────────────────────────────────────────────────────
        $email = $input->getArgument('email');
        if (!$email) {
            $email = $io->ask(
                'E-mail',
                null,
                function (string $value) use ($validator): string {
                    $violations = $validator->validate($value, [
                        new NotBlank(['message' => 'O e-mail é obrigatório.']),
                        new Email(['message'   => 'Informe um e-mail válido.']),
                    ]);
                    if (count($violations) > 0) {
                        throw new \RuntimeException((string) $violations->get(0)->getMessage());
                    }
                    return strtolower(trim($value));
                }
            );
        }

        // Verificar se e-mail já existe
        if ($this->userRepository->findOneBy(['email' => $email])) {
            $io->error("Já existe um usuário com o e-mail \"$email\".");
            return Command::FAILURE;
        }

        // ── Nome (opcional) ───────────────────────────────────────────────────
        $name = $io->ask('Nome completo (opcional — Enter para pular)', null);

        // ── Senha ─────────────────────────────────────────────────────────────
        $password = $input->getArgument('password');
        if (!$password) {
            $password = $io->askHidden(
                'Senha (mínimo 8 caracteres)',
                function (string $value) use ($validator): string {
                    $violations = $validator->validate($value, [
                        new NotBlank(['message' => 'A senha não pode ser vazia.']),
                        new Length([
                            'min'        => 8,
                            'minMessage' => 'A senha deve ter pelo menos {{ limit }} caracteres.',
                        ]),
                    ]);
                    if (count($violations) > 0) {
                        throw new \RuntimeException((string) $violations->get(0)->getMessage());
                    }
                    return $value;
                }
            );

            // Confirmação de senha
            $io->askHidden(
                'Confirmar senha',
                function (string $confirm) use ($password): string {
                    if ($confirm !== $password) {
                        throw new \RuntimeException('As senhas não conferem.');
                    }
                    return $confirm;
                }
            );
        }

        // ── Resumo antes de confirmar ─────────────────────────────────────────
        $io->table(
            ['Campo', 'Valor'],
            [
                ['Username', $username],
                ['E-mail',   $email],
                ['Nome',     $name ?: '(não informado)'],
                ['Role',     'ROLE_ADMIN'],
            ]
        );

        if (!$io->confirm('Confirmar criação do usuário?', true)) {
            $io->warning('Operação cancelada.');
            return Command::SUCCESS;
        }

        // ── Criar e persistir ─────────────────────────────────────────────────
        $user = new User();
        $user->setUsername($username);
        $user->setEmail($email);
        $user->setName($name ?: null);
        $user->setRoles(['ROLE_ADMIN']);
        $user->setPassword(
            $this->userPasswordHasher->hashPassword($user, $password)
        );

        $this->em->persist($user);
        $this->em->flush();

        $io->success([
            'Usuário administrador criado com sucesso!',
            "Username : $username",
            "E-mail   : $email",
        ]);

        return Command::SUCCESS;
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function listUsers(SymfonyStyle $io): void
    {
        $io->title('Usuários cadastrados');
        $users = $this->userRepository->findBy([], ['id' => 'ASC']);

        if (empty($users)) {
            $io->warning('Nenhum usuário encontrado no sistema.');
            return;
        }

        $rows = array_map(function (User $u) {
            $roles = array_filter($u->getRoles(), fn($r) => $r !== 'ROLE_USER');
            $roleLabels = array_map(fn($r) => match ($r) {
                'ROLE_ADMIN' => '★ Admin',
                default      => ucfirst(strtolower(str_replace(['ROLE_', '_'], ['', ' '], $r))),
            }, $roles);

            return [
                $u->getId(),
                $u->getUsername(),
                $u->getName() ?? '—',
                $u->getEmail() ?? '—',
                implode(', ', $roleLabels) ?: 'Usuário',
                $u->getCreatedAt()?->format('d/m/Y') ?? '—',
            ];
        }, $users);

        $io->table(
            ['ID', 'Username', 'Nome', 'E-mail', 'Roles', 'Criado em'],
            $rows
        );

        $io->text(sprintf('<info>Total: %d usuário(s)</info>', count($users)));
    }
}
