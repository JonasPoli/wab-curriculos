<?php

namespace App\Entity;

use App\Contract\TenantAwareInterface;
use App\Repository\CandidateRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CandidateRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Candidate implements TenantAwareInterface, UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Tenant $tenant = null;

    #[ORM\OneToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?User $user = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $password = null;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private array $roles = [];

    #[ORM\Column(length: 150)]
    #[Assert\NotBlank(message: 'O nome é obrigatório.', groups: ['Default', 'Registration'])]
    #[Assert\Length(max: 150, groups: ['Default', 'Registration'])]
    private ?string $name = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $birthDate = null;

    #[ORM\Column(length: 180)]
    #[Assert\NotBlank(message: 'O e-mail é obrigatório.', groups: ['Default', 'Registration'])]
    #[Assert\Email(message: 'Informe um e-mail válido.', groups: ['Default', 'Registration'])]
    private ?string $email = null;

    #[ORM\Column(length: 20)]
    #[Assert\NotBlank(message: 'O telefone é obrigatório.', groups: ['Default', 'Registration'])]
    private ?string $phone = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message: 'A cidade é obrigatória.', groups: ['Default', 'Registration'])]
    private ?string $city = null;

    #[ORM\Column(length: 2)]
    #[Assert\NotBlank(message: 'O estado é obrigatório.', groups: ['Default', 'Registration'])]
    #[Assert\Length(max: 2, maxMessage: 'Informe a sigla do estado (ex: SP).', groups: ['Default', 'Registration'])]
    private ?string $state = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Url(message: 'Informe uma URL válida para o LinkedIn.')]
    private ?string $linkedinUrl = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Url(message: 'Informe uma URL válida para o Lattes.')]
    private ?string $lattesUrl = null;

    #[ORM\Column(options: ['default' => false])]
    private bool $activeRegistration = false;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $councilName = null;

    #[ORM\Column(length: 30, nullable: true)]
    private ?string $registrationNumber = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Assert\Length(max: 1500)]
    private ?string $professionalSummary = null;

    #[ORM\Column(nullable: true)]
    private ?array $contractTypes = null;

    #[ORM\Column(options: ['default' => false])]
    private bool $immediateStart = false;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $resumeFilename = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Assert\Length(max: 1000)]
    private ?string $candidateMessage = null;

    // ─── Disponibilidade semanal ──────────────────────────────────────────────

    #[ORM\Column(options: ['default' => false])]
    private bool $mondayMorning = false;
    #[ORM\Column(options: ['default' => false])]
    private bool $mondayAfternoon = false;
    #[ORM\Column(options: ['default' => false])]
    private bool $tuesdayMorning = false;
    #[ORM\Column(options: ['default' => false])]
    private bool $tuesdayAfternoon = false;
    #[ORM\Column(options: ['default' => false])]
    private bool $wednesdayMorning = false;
    #[ORM\Column(options: ['default' => false])]
    private bool $wednesdayAfternoon = false;
    #[ORM\Column(options: ['default' => false])]
    private bool $thursdayMorning = false;
    #[ORM\Column(options: ['default' => false])]
    private bool $thursdayAfternoon = false;
    #[ORM\Column(options: ['default' => false])]
    private bool $fridayMorning = false;
    #[ORM\Column(options: ['default' => false])]
    private bool $fridayAfternoon = false;

    // ─── LGPD ────────────────────────────────────────────────────────────────

    #[ORM\Column(options: ['default' => false])]
    private bool $lgpdConsent = false;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTime $lgpdConsentAt = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $lgpdConsentIp = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $lgpdConsentUserAgent = null;

    // ─── Timestamps ───────────────────────────────────────────────────────────

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTime $updatedAt = null;

    // ─── Reset de senha ──────────────────────────────────────────────────────

    #[ORM\Column(length: 100, nullable: true, unique: true)]
    private ?string $resetToken = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $resetTokenExpiresAt = null;

    // ─── Relacionamentos ─────────────────────────────────────────────────────

    /**
     * @var Collection<int, Career>
     */
    #[ORM\ManyToMany(targetEntity: Career::class)]
    #[ORM\JoinTable(name: 'candidate_career')]
    private Collection $careers;

    /**
     * @var Collection<int, WorkExperience>
     */
    #[ORM\OneToMany(targetEntity: WorkExperience::class, mappedBy: 'candidate', cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[ORM\OrderBy(['startDate' => 'DESC'])]
    private Collection $workExperiences;

    /**
     * @var Collection<int, AcademicBackground>
     */
    #[ORM\OneToMany(targetEntity: AcademicBackground::class, mappedBy: 'candidate', cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $academicBackgrounds;

    public function __construct()
    {
        $this->careers            = new ArrayCollection();
        $this->workExperiences    = new ArrayCollection();
        $this->academicBackgrounds = new ArrayCollection();
    }

    // ─── Lifecycle Callbacks ──────────────────────────────────────────────────

    #[ORM\PrePersist]
    public function onPrePersist(): void
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTime();
    }

    #[ORM\PreUpdate]
    public function onPreUpdate(): void
    {
        $this->updatedAt = new \DateTime();
    }

    // ─── Getters & Setters ────────────────────────────────────────────────────

    public function getId(): ?int { return $this->id; }

    public function getTenant(): ?Tenant { return $this->tenant; }
    public function setTenant(?Tenant $tenant): static { $this->tenant = $tenant; return $this; }

    public function getUser(): ?User { return $this->user; }
    public function setUser(?User $user): static { $this->user = $user; return $this; }

    // ─── UserInterface ────────────────────────────────────────────────────────

    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    public function getRoles(): array
    {
        $roles = $this->roles;
        $roles[] = 'ROLE_CANDIDATE';
        return array_unique($roles);
    }

    public function setRoles(array $roles): static { $this->roles = $roles; return $this; }

    public function getPassword(): ?string { return $this->password; }
    public function setPassword(?string $password): static { $this->password = $password; return $this; }

    public function eraseCredentials(): void {}

    public function getName(): ?string { return $this->name; }
    public function setName(string $name): static { $this->name = $name; return $this; }

    public function getBirthDate(): ?\DateTimeImmutable { return $this->birthDate; }
    public function setBirthDate(?\DateTimeImmutable $birthDate): static { $this->birthDate = $birthDate; return $this; }

    public function getEmail(): ?string { return $this->email; }
    public function setEmail(string $email): static { $this->email = $email; return $this; }

    public function getPhone(): ?string { return $this->phone; }
    public function setPhone(string $phone): static { $this->phone = $phone; return $this; }

    public function getCity(): ?string { return $this->city; }
    public function setCity(string $city): static { $this->city = $city; return $this; }

    public function getState(): ?string { return $this->state; }
    public function setState(string $state): static { $this->state = $state; return $this; }

    public function getLinkedinUrl(): ?string { return $this->linkedinUrl; }
    public function setLinkedinUrl(?string $linkedinUrl): static { $this->linkedinUrl = $linkedinUrl; return $this; }

    public function getLattesUrl(): ?string { return $this->lattesUrl; }
    public function setLattesUrl(?string $lattesUrl): static { $this->lattesUrl = $lattesUrl; return $this; }

    public function isActiveRegistration(): bool { return $this->activeRegistration; }
    public function setActiveRegistration(bool $activeRegistration): static { $this->activeRegistration = $activeRegistration; return $this; }

    public function getCouncilName(): ?string { return $this->councilName; }
    public function setCouncilName(?string $councilName): static { $this->councilName = $councilName; return $this; }

    public function getRegistrationNumber(): ?string { return $this->registrationNumber; }
    public function setRegistrationNumber(?string $registrationNumber): static { $this->registrationNumber = $registrationNumber; return $this; }

    public function getProfessionalSummary(): ?string { return $this->professionalSummary; }
    public function setProfessionalSummary(?string $professionalSummary): static { $this->professionalSummary = $professionalSummary; return $this; }

    public function getContractTypes(): ?array { return $this->contractTypes; }
    public function setContractTypes(?array $contractTypes): static { $this->contractTypes = $contractTypes; return $this; }

    public function isImmediateStart(): bool { return $this->immediateStart; }
    public function setImmediateStart(bool $immediateStart): static { $this->immediateStart = $immediateStart; return $this; }

    public function getResumeFilename(): ?string { return $this->resumeFilename; }
    public function setResumeFilename(?string $resumeFilename): static { $this->resumeFilename = $resumeFilename; return $this; }

    public function getCandidateMessage(): ?string { return $this->candidateMessage; }
    public function setCandidateMessage(?string $candidateMessage): static { $this->candidateMessage = $candidateMessage; return $this; }

    // ─── Disponibilidade ─────────────────────────────────────────────────────

    public function isMondayMorning(): bool { return $this->mondayMorning; }
    public function setMondayMorning(bool $v): static { $this->mondayMorning = $v; return $this; }
    public function isMondayAfternoon(): bool { return $this->mondayAfternoon; }
    public function setMondayAfternoon(bool $v): static { $this->mondayAfternoon = $v; return $this; }
    public function isTuesdayMorning(): bool { return $this->tuesdayMorning; }
    public function setTuesdayMorning(bool $v): static { $this->tuesdayMorning = $v; return $this; }
    public function isTuesdayAfternoon(): bool { return $this->tuesdayAfternoon; }
    public function setTuesdayAfternoon(bool $v): static { $this->tuesdayAfternoon = $v; return $this; }
    public function isWednesdayMorning(): bool { return $this->wednesdayMorning; }
    public function setWednesdayMorning(bool $v): static { $this->wednesdayMorning = $v; return $this; }
    public function isWednesdayAfternoon(): bool { return $this->wednesdayAfternoon; }
    public function setWednesdayAfternoon(bool $v): static { $this->wednesdayAfternoon = $v; return $this; }
    public function isThursdayMorning(): bool { return $this->thursdayMorning; }
    public function setThursdayMorning(bool $v): static { $this->thursdayMorning = $v; return $this; }
    public function isThursdayAfternoon(): bool { return $this->thursdayAfternoon; }
    public function setThursdayAfternoon(bool $v): static { $this->thursdayAfternoon = $v; return $this; }
    public function isFridayMorning(): bool { return $this->fridayMorning; }
    public function setFridayMorning(bool $v): static { $this->fridayMorning = $v; return $this; }
    public function isFridayAfternoon(): bool { return $this->fridayAfternoon; }
    public function setFridayAfternoon(bool $v): static { $this->fridayAfternoon = $v; return $this; }

    // ─── LGPD ────────────────────────────────────────────────────────────────

    public function isLgpdConsent(): bool { return $this->lgpdConsent; }
    public function setLgpdConsent(bool $lgpdConsent): static { $this->lgpdConsent = $lgpdConsent; return $this; }

    public function getLgpdConsentAt(): ?\DateTime { return $this->lgpdConsentAt; }
    public function setLgpdConsentAt(?\DateTime $lgpdConsentAt): static { $this->lgpdConsentAt = $lgpdConsentAt; return $this; }

    public function getLgpdConsentIp(): ?string { return $this->lgpdConsentIp; }
    public function setLgpdConsentIp(?string $lgpdConsentIp): static { $this->lgpdConsentIp = $lgpdConsentIp; return $this; }

    public function getLgpdConsentUserAgent(): ?string { return $this->lgpdConsentUserAgent; }
    public function setLgpdConsentUserAgent(?string $lgpdConsentUserAgent): static { $this->lgpdConsentUserAgent = $lgpdConsentUserAgent; return $this; }

    // ─── Timestamps ───────────────────────────────────────────────────────────

    public function getCreatedAt(): ?\DateTimeImmutable { return $this->createdAt; }
    public function getUpdatedAt(): ?\DateTime { return $this->updatedAt; }

    // ─── Relacionamentos ─────────────────────────────────────────────────────

    /** @return Collection<int, Career> */
    public function getCareers(): Collection { return $this->careers; }

    public function addCareer(Career $career): static
    {
        if (!$this->careers->contains($career)) {
            $this->careers->add($career);
        }
        return $this;
    }

    public function removeCareer(Career $career): static
    {
        $this->careers->removeElement($career);
        return $this;
    }

    /** @return Collection<int, WorkExperience> */
    public function getWorkExperiences(): Collection { return $this->workExperiences; }

    public function addWorkExperience(WorkExperience $we): static
    {
        if (!$this->workExperiences->contains($we)) {
            $this->workExperiences->add($we);
            $we->setCandidate($this);
        }
        return $this;
    }

    public function removeWorkExperience(WorkExperience $we): static
    {
        if ($this->workExperiences->removeElement($we)) {
            if ($we->getCandidate() === $this) {
                $we->setCandidate(null);
            }
        }
        return $this;
    }

    /** @return Collection<int, AcademicBackground> */
    public function getAcademicBackgrounds(): Collection { return $this->academicBackgrounds; }

    public function addAcademicBackground(AcademicBackground $ab): static
    {
        if (!$this->academicBackgrounds->contains($ab)) {
            $this->academicBackgrounds->add($ab);
            $ab->setCandidate($this);
        }
        return $this;
    }

    public function removeAcademicBackground(AcademicBackground $ab): static
    {
        if ($this->academicBackgrounds->removeElement($ab)) {
            if ($ab->getCandidate() === $this) {
                $ab->setCandidate(null);
            }
        }
        return $this;
    }

    public function getAvailabilityGrid(): array
    {
        return [
            'Segunda'   => ['morning' => $this->mondayMorning,    'afternoon' => $this->mondayAfternoon],
            'Terça'     => ['morning' => $this->tuesdayMorning,   'afternoon' => $this->tuesdayAfternoon],
            'Quarta'    => ['morning' => $this->wednesdayMorning, 'afternoon' => $this->wednesdayAfternoon],
            'Quinta'    => ['morning' => $this->thursdayMorning,  'afternoon' => $this->thursdayAfternoon],
            'Sexta'     => ['morning' => $this->fridayMorning,    'afternoon' => $this->fridayAfternoon],
        ];
    }

    public function getResetToken(): ?string { return $this->resetToken; }
    public function setResetToken(?string $t): static { $this->resetToken = $t; return $this; }
    public function getResetTokenExpiresAt(): ?\DateTimeImmutable { return $this->resetTokenExpiresAt; }
    public function setResetTokenExpiresAt(?\DateTimeImmutable $t): static { $this->resetTokenExpiresAt = $t; return $this; }
}
