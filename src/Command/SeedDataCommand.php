<?php

namespace App\Command;

use App\Entity\AcademicBackground;
use App\Entity\AreaOfInterest;
use App\Entity\Candidate;
use App\Entity\Career;
use App\Entity\Tenant;
use App\Entity\User;
use App\Entity\WorkExperience;
use App\Enum\AcademicStatus;
use App\Enum\ContractType;
use App\Enum\EducationLevel;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:seed',
    description: 'Injeta dados de exemplo no banco de dados (tenants, áreas, cargos, candidatos)',
)]
class SeedDataCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly UserPasswordHasherInterface $passwordHasher,
        #[Autowire('%kernel.project_dir%')]
        private readonly string $projectDir,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption('reset', null, InputOption::VALUE_NONE, 'Apaga todos os dados antes de reinjetar');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $fs = new Filesystem();

        if ($input->getOption('reset')) {
            $io->warning('Apagando dados existentes...');
            $this->truncateAll();
            $io->success('Dados apagados.');
        }

        $io->title('🌱 Injetando dados de exemplo');

        // ── Tenants ────────────────────────────────────────────────────────────
        $io->section('Criando Tenants');

        $tenants = [
            [
                'name'               => 'Procordis Hospital',
                'domain'             => '127.0.0.1',
                'primaryColor'       => '#0f4c81',
                'secondaryColor'     => '#e8a020',
                'primaryColorDark'   => '#3b82f6',
                'secondaryColorDark' => '#f59e0b',
                'contactEmail'       => 'rh@procordis.com.br',
                'phone'              => '(11) 3456-7890',
                'address'            => 'Av. Paulista, 1500 - Bela Vista, Sao Paulo - SP',
                'logo'               => 'logo-procordis.png',
                'heroTitle'          => 'Faca parte do time Procordis',
                'heroSubtitle'       => 'Ha mais de 20 anos cuidando de vidas. Cadastre seu curriculo e construa uma carreira na area da saude.',
                'heroDescription'    => 'O Hospital Procordis e referencia em cardiologia e cirurgia cardiovascular. Valorizamos profissionais comprometidos com a excelencia no atendimento ao paciente. Oferecemos um ambiente de trabalho colaborativo, programas de desenvolvimento profissional e beneficios competitivos.',
                'ctaText'            => 'Cadastre seu curriculo',
                'ctaSubtext'         => 'Processo seguro e rapido',
                'seoTitle'           => 'Trabalhe Conosco - Procordis Hospital',
                'seoDescription'     => 'Cadastre seu curriculo e faca parte do time do Hospital Procordis. Vagas em enfermagem, medicina, administrativo e TI.',
            ],
            [
                'name'               => 'Vida & Saude Clinica',
                'domain'             => 'vidasaude.localhost',
                'primaryColor'       => '#16a34a',
                'secondaryColor'     => '#f97316',
                'primaryColorDark'   => '#22c55e',
                'secondaryColorDark' => '#fb923c',
                'contactEmail'       => 'vagas@vidasaude.com.br',
                'phone'              => '(21) 2345-6789',
                'address'            => 'Rua Voluntarios da Patria, 300 - Botafogo, Rio de Janeiro - RJ',
                'logo'               => 'logo-vidasaude.png',
                'heroTitle'          => 'Venha trabalhar na Vida & Saude',
                'heroSubtitle'       => 'Uma clinica que valoriza seus profissionais. Cadastre-se e faca parte da nossa equipe multidisciplinar.',
                'heroDescription'    => 'A Clinica Vida & Saude oferece atendimento integrado em psicologia, fisioterapia, nutricao e fonoaudiologia. Buscamos profissionais dedicados que compartilhem nossa visao de saude humanizada.',
                'ctaText'            => 'Quero fazer parte',
                'ctaSubtext'         => 'Cadastro simples e gratuito',
                'seoTitle'           => 'Trabalhe Conosco - Vida & Saude Clinica',
                'seoDescription'     => 'Oportunidades em psicologia, fisioterapia, farmacia e administrativo na Clinica Vida & Saude.',
            ],
        ];

        $tenantEntities = [];
        foreach ($tenants as $data) {
            $existing = $this->em->getRepository(Tenant::class)->findOneBy(['domain' => $data['domain']]);
            if ($existing) {
                $io->text("  <comment>Tenant '{$data['name']}' já existe, pulando.</comment>");
                $tenantEntities[] = $existing;
                continue;
            }

            $tenant = new Tenant();
            $tenant->setName($data['name']);
            $tenant->setDomain($data['domain']);
            $tenant->setPrimaryColor($data['primaryColor']);
            $tenant->setSecondaryColor($data['secondaryColor']);
            $tenant->setPrimaryColorDark($data['primaryColorDark']);
            $tenant->setSecondaryColorDark($data['secondaryColorDark']);
            $tenant->setContactEmail($data['contactEmail']);
            $tenant->setPhone($data['phone'] ?? null);
            $tenant->setAddress($data['address'] ?? null);
            $tenant->setHeroTitle($data['heroTitle'] ?? null);
            $tenant->setHeroSubtitle($data['heroSubtitle'] ?? null);
            $tenant->setHeroDescription($data['heroDescription'] ?? null);
            $tenant->setCtaText($data['ctaText'] ?? null);
            $tenant->setCtaSubtext($data['ctaSubtext'] ?? null);
            $tenant->setSeoTitle($data['seoTitle'] ?? null);
            $tenant->setSeoDescription($data['seoDescription'] ?? null);

            $this->copyLogoToTenant($fs, $data['logo'], $tenant);

            $this->em->persist($tenant);
            $tenantEntities[] = $tenant;
            $io->text("  <info>✔</info> Tenant '{$data['name']}' criado");
        }

        $this->em->flush();

        // ── Áreas de Interesse ─────────────────────────────────────────────────
        $io->section('Criando Áreas de Interesse e Cargos');

        $areasData = [
            'Procordis Hospital' => [
                ['title' => 'Enfermagem', 'careers' => ['Enfermeiro(a)', 'Técnico(a) de Enfermagem', 'Auxiliar de Enfermagem']],
                ['title' => 'Medicina', 'careers' => ['Médico(a) Clínico Geral', 'Médico(a) Plantonista', 'Médico(a) Especialista']],
                ['title' => 'Administrativo', 'careers' => ['Recepcionista', 'Assistente Administrativo', 'Auxiliar de Faturamento']],
                ['title' => 'Tecnologia da Informação', 'careers' => ['Analista de Sistemas', 'Suporte de TI']],
            ],
            'Vida & Saude Clinica' => [
                ['title' => 'Saúde', 'careers' => ['Psicólogo(a)', 'Fisioterapeuta', 'Nutricionista', 'Fonoaudiólogo(a)']],
                ['title' => 'Administrativo', 'careers' => ['Recepcionista', 'Coordenador(a) Administrativo']],
                ['title' => 'Farmácia', 'careers' => ['Farmacêutico(a)', 'Auxiliar de Farmácia']],
            ],
        ];

        foreach ($tenantEntities as $tenant) {
            $areas = $areasData[$tenant->getName()] ?? [];
            foreach ($areas as $pos => $areaData) {
                $area = new AreaOfInterest();
                $area->setTitle($areaData['title']);
                $area->setTenant($tenant);
                $area->setPosition($pos);
                $this->em->persist($area);

                foreach ($areaData['careers'] as $careerPos => $careerTitle) {
                    $career = new Career();
                    $career->setTitle($careerTitle);
                    $career->setArea($area);
                    $career->setPosition($careerPos);
                    $career->setActive(true);
                    $this->em->persist($career);
                }

                $io->text("  <info>✔</info> [{$tenant->getName()}] Área '{$areaData['title']}' + " . count($areaData['careers']) . ' cargos');
            }
        }

        $this->em->flush();

        // ── Candidatos ─────────────────────────────────────────────────────────
        $io->section('Criando Candidatos');

        $candidatesData = [
            'Procordis Hospital' => [
                [
                    'name' => 'Ana Paula Ferreira',
                    'email' => 'ana.ferreira@email.com',
                    'phone' => '(11) 99201-3344',
                    'city' => 'São Paulo', 'state' => 'SP',
                    'birthDate' => '1988-04-15',
                    'professionalSummary' => 'Enfermeira graduada com 8 anos de experiência em UTI adulto e pronto-socorro. Especialização em Enfermagem Intensiva. Busco oportunidade em ambiente hospitalar de alta complexidade.',
                    'councilName' => 'COREN-SP', 'registrationNumber' => '123456',
                    'contractTypes' => ['CLT'],
                    'immediateStart' => true,
                    'experiences' => [
                        ['company' => 'Hospital das Clínicas USP', 'role' => 'Enfermeira UTI', 'start' => '2016-03', 'end' => null, 'current' => true, 'description' => 'Cuidados de alta complexidade em UTI adulto com 30 leitos.'],
                        ['company' => 'UPA Zona Sul', 'role' => 'Enfermeira Pronto-Atendimento', 'start' => '2013-06', 'end' => '2016-02', 'current' => false, 'description' => 'Triagem e atendimento de emergência.'],
                    ],
                    'academics' => [
                        ['institution' => 'USP', 'course' => 'Enfermagem', 'level' => EducationLevel::SUPERIOR_COMP, 'startYear' => 2009, 'endYear' => 2013, 'status' => AcademicStatus::CONCLUIDO],
                        ['institution' => 'UNASUS', 'course' => 'Enfermagem Intensiva', 'level' => EducationLevel::POS_GRADUACAO, 'startYear' => 2014, 'endYear' => 2015, 'status' => AcademicStatus::CONCLUIDO],
                    ],
                ],
                [
                    'name' => 'Carlos Eduardo Souza',
                    'email' => 'carlos.souza@email.com',
                    'phone' => '(11) 98765-4321',
                    'city' => 'Guarulhos', 'state' => 'SP',
                    'birthDate' => '1992-11-30',
                    'professionalSummary' => 'Técnico de Enfermagem com 5 anos de experiência em enfermaria cirúrgica e centro cirúrgico. Disponível para turnos noturnos.',
                    'councilName' => 'COREN-SP', 'registrationNumber' => '654321',
                    'contractTypes' => ['CLT', 'PJ'],
                    'immediateStart' => true,
                    'experiences' => [
                        ['company' => 'Hospital Santa Cruz', 'role' => 'Técnico de Enfermagem', 'start' => '2019-01', 'end' => null, 'current' => true, 'description' => 'Assistência em centro cirúrgico e recuperação anestésica.'],
                    ],
                    'academics' => [
                        ['institution' => 'SENAC SP', 'course' => 'Técnico de Enfermagem', 'level' => EducationLevel::TECNICO, 'startYear' => 2017, 'endYear' => 2019, 'status' => AcademicStatus::CONCLUIDO],
                    ],
                ],
                [
                    'name' => 'Mariana Costa Lima',
                    'email' => 'mariana.lima@email.com',
                    'phone' => '(11) 97654-8800',
                    'city' => 'São Bernardo do Campo', 'state' => 'SP',
                    'birthDate' => '1985-07-22',
                    'professionalSummary' => 'Médica com CRM ativo, especialização em Clínica Médica. Experiência em atendimento ambulatorial e pronto-socorro.',
                    'councilName' => 'CRM-SP', 'registrationNumber' => '00012345',
                    'contractTypes' => ['PJ'],
                    'immediateStart' => false,
                    'experiences' => [
                        ['company' => 'Rede Amigo', 'role' => 'Médica Plantonista', 'start' => '2015-03', 'end' => null, 'current' => true, 'description' => 'Plantões de 12h em pronto-atendimento.'],
                    ],
                    'academics' => [
                        ['institution' => 'FMUSP', 'course' => 'Medicina', 'level' => EducationLevel::SUPERIOR_COMP, 'startYear' => 2004, 'endYear' => 2010, 'status' => AcademicStatus::CONCLUIDO],
                        ['institution' => 'Hospital das Clínicas', 'course' => 'Residência em Clínica Médica', 'level' => EducationLevel::POS_GRADUACAO, 'startYear' => 2010, 'endYear' => 2012, 'status' => AcademicStatus::CONCLUIDO],
                    ],
                ],
            ],
            'Vida & Saude Clinica' => [
                [
                    'name' => 'Beatriz Oliveira Santos',
                    'email' => 'beatriz.santos@email.com',
                    'phone' => '(21) 99100-2233',
                    'city' => 'Rio de Janeiro', 'state' => 'RJ',
                    'birthDate' => '1990-02-14',
                    'professionalSummary' => 'Psicóloga clínica com CRP ativo. Atendimento de adultos com foco em TCC. Experiência em clínicas e atendimento online.',
                    'councilName' => 'CRP-06', 'registrationNumber' => '112233',
                    'contractTypes' => ['PJ'],
                    'immediateStart' => true,
                    'experiences' => [
                        ['company' => 'Clínica Mente Sã', 'role' => 'Psicóloga Clínica', 'start' => '2017-08', 'end' => null, 'current' => true, 'description' => 'Psicoterapia individual adultos e grupos de apoio.'],
                    ],
                    'academics' => [
                        ['institution' => 'PUC-Rio', 'course' => 'Psicologia', 'level' => EducationLevel::SUPERIOR_COMP, 'startYear' => 2008, 'endYear' => 2013, 'status' => AcademicStatus::CONCLUIDO],
                        ['institution' => 'ABCT', 'course' => 'Especialização em TCC', 'level' => EducationLevel::POS_GRADUACAO, 'startYear' => 2014, 'endYear' => 2016, 'status' => AcademicStatus::CONCLUIDO],
                    ],
                ],
                [
                    'name' => 'Ricardo Alves Pereira',
                    'email' => 'ricardo.pereira@email.com',
                    'phone' => '(21) 98877-6655',
                    'city' => 'Niterói', 'state' => 'RJ',
                    'birthDate' => '1994-09-05',
                    'professionalSummary' => 'Fisioterapeuta com ênfase em reabilitação ortopédica e esportiva. CREFITO ativo.',
                    'councilName' => 'CREFITO-2', 'registrationNumber' => '987654',
                    'contractTypes' => ['CLT'],
                    'immediateStart' => true,
                    'experiences' => [
                        ['company' => 'Centro de Reabilitação Pinheiros', 'role' => 'Fisioterapeuta', 'start' => '2018-02', 'end' => '2023-12', 'current' => false, 'description' => 'Atendimento ortopédico e pós-operatório.'],
                        ['company' => 'Clínica Autônomo', 'role' => 'Fisioterapeuta', 'start' => '2024-01', 'end' => null, 'current' => true, 'description' => 'Atendimento domiciliar e em consultório.'],
                    ],
                    'academics' => [
                        ['institution' => 'UFRJ', 'course' => 'Fisioterapia', 'level' => EducationLevel::SUPERIOR_COMP, 'startYear' => 2012, 'endYear' => 2017, 'status' => AcademicStatus::CONCLUIDO],
                    ],
                ],
            ],
        ];

        foreach ($tenantEntities as $tenant) {
            $candidates = $candidatesData[$tenant->getName()] ?? [];
            foreach ($candidates as $data) {
                $existing = $this->em->getRepository(Candidate::class)->findOneBy(['email' => $data['email']]);
                if ($existing) {
                    $io->text("  <comment>Candidato '{$data['name']}' já existe, pulando.</comment>");
                    continue;
                }

                $candidate = new Candidate();
                $candidate->setTenant($tenant);
                $candidate->setName($data['name']);
                $candidate->setEmail($data['email']);
                $candidate->setPhone($data['phone']);
                $candidate->setCity($data['city']);
                $candidate->setState($data['state']);
                $candidate->setBirthDate(new \DateTimeImmutable($data['birthDate']));
                $candidate->setProfessionalSummary($data['professionalSummary']);
                $candidate->setCouncilName($data['councilName'] ?? null);
                $candidate->setRegistrationNumber($data['registrationNumber'] ?? null);
                $candidate->setImmediateStart($data['immediateStart']);
                $candidate->setLgpdConsent(true);
                $candidate->setLgpdConsentAt(new \DateTime());
                $candidate->setLgpdConsentIp('127.0.0.1');
                $candidate->setActiveRegistration(true);

                $contractEnums = [];
                foreach ($data['contractTypes'] as $ct) {
                    $contractEnums[] = ContractType::from($ct);
                }
                $candidate->setContractTypes($contractEnums);

                $this->em->persist($candidate);

                foreach ($data['experiences'] as $exp) {
                    $we = new WorkExperience();
                    $we->setCandidate($candidate);
                    $we->setCompanyName($exp['company']);
                    $we->setPosition($exp['role']);
                    $we->setStartDate(new \DateTimeImmutable($exp['start'] . '-01'));
                    $we->setEndDate($exp['current'] || !$exp['end'] ? null : new \DateTimeImmutable($exp['end'] . '-01'));
                    $we->setCurrentJob($exp['current']);
                    $we->setDescription($exp['description']);
                    $this->em->persist($we);
                }

                foreach ($data['academics'] as $acad) {
                    $ab = new AcademicBackground();
                    $ab->setCandidate($candidate);
                    $ab->setInstitution($acad['institution']);
                    $ab->setCourse($acad['course']);
                    $ab->setEducationLevel($acad['level']);
                    $ab->setStartDate(new \DateTimeImmutable($acad['startYear'] . '-01-01'));
                    $ab->setEndDate(isset($acad['endYear']) ? new \DateTimeImmutable($acad['endYear'] . '-12-01') : null);
                    $ab->setStatus($acad['status']);
                    $this->em->persist($ab);
                }

                $io->text("  <info>✔</info> [{$tenant->getName()}] Candidato '{$data['name']}' criado");
            }
        }

        $this->em->flush();

        $io->success('Seed concluído com sucesso!');
        $io->table(
            ['Tenant', 'Áreas', 'Cargos', 'Candidatos'],
            array_map(fn(Tenant $t) => [
                $t->getName(),
                count($areasData[$t->getName()] ?? []),
                array_sum(array_map(fn($a) => count($a['careers']), $areasData[$t->getName()] ?? [])),
                count($candidatesData[$t->getName()] ?? []),
            ], $tenantEntities)
        );

        return Command::SUCCESS;
    }

    private function copyLogoToTenant(Filesystem $fs, string $filename, Tenant $tenant): void
    {
        $src = $this->projectDir . '/assets/seed-images/' . $filename;
        if (!$fs->exists($src)) {
            return;
        }

        $destDir = $this->projectDir . '/public/uploads/tenants/logo';
        $fs->mkdir($destDir);
        $fs->copy($src, $destDir . '/' . $filename);
        $tenant->setLogo($filename);
    }

    private function truncateAll(): void
    {
        $connection = $this->em->getConnection();
        $connection->executeStatement('SET FOREIGN_KEY_CHECKS=0');
        foreach (['saved_search', 'academic_background', 'work_experience', 'candidate', 'career', 'area_of_interest', 'lgpd_log', 'exclusion_request'] as $table) {
            $connection->executeStatement("TRUNCATE TABLE `$table`");
        }
        $connection->executeStatement('SET FOREIGN_KEY_CHECKS=1');
    }
}
