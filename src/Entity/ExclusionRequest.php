<?php

namespace App\Entity;

use App\Contract\TenantAwareInterface;
use App\Repository\ExclusionRequestRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ExclusionRequestRepository::class)]
class ExclusionRequest implements TenantAwareInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Tenant $tenant = null;

    #[ORM\Column(length: 180)]
    private ?string $email = null;

    #[ORM\Column(length: 100, unique: true)]
    private ?string $token = null;

    #[ORM\Column(options: ['default' => false])]
    private bool $confirmed = false;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private \DateTime $requestedAt;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTime $confirmedAt = null;

    public function __construct()
    {
        $this->requestedAt = new \DateTime();
        $this->token       = bin2hex(random_bytes(32));
    }

    public function getId(): ?int { return $this->id; }

    public function getTenant(): ?Tenant { return $this->tenant; }
    public function setTenant(?Tenant $tenant): static { $this->tenant = $tenant; return $this; }

    public function getEmail(): ?string { return $this->email; }
    public function setEmail(string $email): static { $this->email = $email; return $this; }

    public function getToken(): ?string { return $this->token; }
    public function setToken(string $token): static { $this->token = $token; return $this; }

    public function isConfirmed(): bool { return $this->confirmed; }
    public function setConfirmed(bool $confirmed): static { $this->confirmed = $confirmed; return $this; }

    public function getRequestedAt(): \DateTime { return $this->requestedAt; }

    public function getConfirmedAt(): ?\DateTime { return $this->confirmedAt; }
    public function setConfirmedAt(?\DateTime $confirmedAt): static { $this->confirmedAt = $confirmedAt; return $this; }
}
