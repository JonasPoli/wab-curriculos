<?php

namespace App\Entity;

use App\Enum\LgpdActionType;
use App\Repository\LgpdLogRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LgpdLogRepository::class)]
class LgpdLog
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?Candidate $candidate = null;

    #[ORM\Column(length: 30, enumType: LgpdActionType::class)]
    private ?LgpdActionType $actionType = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private \DateTime $executedAt;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $ipAddress = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $details = null;

    public function __construct()
    {
        $this->executedAt = new \DateTime();
    }

    public function getId(): ?int { return $this->id; }

    public function getCandidate(): ?Candidate { return $this->candidate; }
    public function setCandidate(?Candidate $candidate): static { $this->candidate = $candidate; return $this; }

    public function getActionType(): ?LgpdActionType { return $this->actionType; }
    public function setActionType(LgpdActionType $actionType): static { $this->actionType = $actionType; return $this; }

    public function getExecutedAt(): \DateTime { return $this->executedAt; }
    public function setExecutedAt(\DateTime $executedAt): static { $this->executedAt = $executedAt; return $this; }

    public function getIpAddress(): ?string { return $this->ipAddress; }
    public function setIpAddress(?string $ipAddress): static { $this->ipAddress = $ipAddress; return $this; }

    public function getDetails(): ?string { return $this->details; }
    public function setDetails(?string $details): static { $this->details = $details; return $this; }
}
