<?php

namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:create-super-admin',
    description: 'Cria um usuário Super Admin global (sem tenant).',
)]
class CreateSuperAdminCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly UserPasswordHasherInterface $hasher,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('username', InputArgument::REQUIRED, 'Username para login')
            ->addArgument('email', InputArgument::REQUIRED, 'E-mail do super admin')
            ->addArgument('password', InputArgument::REQUIRED, 'Senha')
            ->addArgument('name', InputArgument::OPTIONAL, 'Nome completo', 'Super Admin')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $user = new User();
        $user->setUsername($input->getArgument('username'));
        $user->setEmail($input->getArgument('email'));
        $user->setName($input->getArgument('name'));
        $user->setPassword($this->hasher->hashPassword($user, $input->getArgument('password')));
        $user->setTenant(null);
        $user->setWorkGroup(null);

        $this->em->persist($user);
        $this->em->flush();

        $io->success(sprintf('Super Admin "%s" criado com sucesso (ID: %d).', $user->getUsername(), $user->getId()));

        return Command::SUCCESS;
    }
}
