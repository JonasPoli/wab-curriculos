<?php

namespace App\Entity;

use App\Repository\WorkExperienceRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: WorkExperienceRepository::class)]
class WorkExperience
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'workExperiences')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Candidate $candidate = null;

    #[ORM\Column(length: 150)]
    #[Assert\NotBlank(message: 'O nome da empresa é obrigatório.')]
    #[Assert\Length(max: 150)]
    private ?string $companyName = null;

    #[ORM\Column(length: 150)]
    #[Assert\NotBlank(message: 'O cargo é obrigatório.')]
    #[Assert\Length(max: 150)]
    private ?string $position = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    #[Assert\NotBlank(message: 'A data de início é obrigatória.')]
    private ?\DateTimeImmutable $startDate = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $endDate = null;

    #[ORM\Column(options: ['default' => false])]
    private bool $currentJob = false;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank(message: 'A descrição das atividades é obrigatória.')]
    #[Assert\Length(max: 1000)]
    private ?string $description = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCandidate(): ?Candidate
    {
        return $this->candidate;
    }

    public function setCandidate(?Candidate $candidate): static
    {
        $this->candidate = $candidate;
        return $this;
    }

    public function getCompanyName(): ?string
    {
        return $this->companyName;
    }

    public function setCompanyName(string $companyName): static
    {
        $this->companyName = $companyName;
        return $this;
    }

    public function getPosition(): ?string
    {
        return $this->position;
    }

    public function setPosition(string $position): static
    {
        $this->position = $position;
        return $this;
    }

    public function getStartDate(): ?\DateTimeImmutable
    {
        return $this->startDate;
    }

    public function setStartDate(\DateTimeImmutable $startDate): static
    {
        $this->startDate = $startDate;
        return $this;
    }

    public function getEndDate(): ?\DateTimeImmutable
    {
        return $this->endDate;
    }

    public function setEndDate(?\DateTimeImmutable $endDate): static
    {
        $this->endDate = $endDate;
        return $this;
    }

    public function isCurrentJob(): bool
    {
        return $this->currentJob;
    }

    public function setCurrentJob(bool $currentJob): static
    {
        $this->currentJob = $currentJob;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;
        return $this;
    }
}
