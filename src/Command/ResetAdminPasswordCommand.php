<?php

namespace App\Command;

use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:reset-admin-password',
    description: 'Redefine a senha de um administrador.',
)]
class ResetAdminPasswordCommand extends Command
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly UserPasswordHasherInterface $userPasswordHasher,
        private readonly EntityManagerInterface $entityManager,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('email', InputArgument::REQUIRED, 'E-mail do administrador')
            ->addArgument('password', InputArgument::REQUIRED, 'Nova senha')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $email = $input->getArgument('email');
        $password = $input->getArgument('password');

        $user = $this->userRepository->findOneBy(['email' => $email]);
        if (!$user) {
            $io->error(sprintf('Usuário com o e-mail "%s" não foi encontrado.', $email));
            return Command::FAILURE;
        }

        $user->setPassword($this->userPasswordHasher->hashPassword($user, $password));
        $this->entityManager->flush();

        $io->success(sprintf('Senha do usuário "%s" (Username: %s) redefinida com sucesso!', $email, $user->getUsername()));

        return Command::SUCCESS;
    }
}
