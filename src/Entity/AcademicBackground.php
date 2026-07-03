<?php

namespace App\Entity;

use App\Enum\AcademicStatus;
use App\Enum\EducationLevel;
use App\Repository\AcademicBackgroundRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: AcademicBackgroundRepository::class)]
class AcademicBackground
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'academicBackgrounds')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Candidate $candidate = null;

    #[ORM\Column(length: 60, enumType: EducationLevel::class)]
    #[Assert\NotBlank(message: 'O nível de escolaridade é obrigatório.')]
    private ?EducationLevel $educationLevel = null;

    #[ORM\Column(length: 150)]
    #[Assert\NotBlank(message: 'O nome da instituição é obrigatório.')]
    #[Assert\Length(max: 150)]
    private ?string $institution = null;

    #[ORM\Column(length: 150)]
    #[Assert\NotBlank(message: 'O nome do curso é obrigatório.')]
    #[Assert\Length(max: 150)]
    private ?string $course = null;

    #[ORM\Column(length: 30, enumType: AcademicStatus::class)]
    #[Assert\NotBlank(message: 'O status do curso é obrigatório.')]
    private ?AcademicStatus $status = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $startDate = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $endDate = null;

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

    public function getEducationLevel(): ?EducationLevel
    {
        return $this->educationLevel;
    }

    public function setEducationLevel(EducationLevel $educationLevel): static
    {
        $this->educationLevel = $educationLevel;
        return $this;
    }

    public function getInstitution(): ?string
    {
        return $this->institution;
    }

    public function setInstitution(string $institution): static
    {
        $this->institution = $institution;
        return $this;
    }

    public function getCourse(): ?string
    {
        return $this->course;
    }

    public function setCourse(string $course): static
    {
        $this->course = $course;
        return $this;
    }

    public function getStatus(): ?AcademicStatus
    {
        return $this->status;
    }

    public function setStatus(AcademicStatus $status): static
    {
        $this->status = $status;
        return $this;
    }

    public function getStartDate(): ?\DateTimeImmutable
    {
        return $this->startDate;
    }

    public function setStartDate(?\DateTimeImmutable $startDate): static
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
}
