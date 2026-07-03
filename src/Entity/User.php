<?php

namespace App\Entity;

use App\Enum\WorkGroup;
use App\Repository\UserRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_USERNAME', fields: ['username'])]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
#[ORM\HasLifecycleCallbacks]
#[UniqueEntity(fields: ['username'], message: 'Já existe um usuário com este username.')]
#[UniqueEntity(fields: ['email'], message: 'Já existe um usuário com este e-mail.')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    #[Assert\NotBlank(message: 'O username é obrigatório.')]
    #[Assert\Length(min: 3, max: 180, minMessage: 'O username deve ter pelo menos {{ limit }} caracteres.')]
    private ?string $username = null;

    #[ORM\Column(length: 180)]
    #[Assert\NotBlank(message: 'O e-mail é obrigatório.')]
    #[Assert\Email(message: 'Informe um e-mail válido.')]
    private ?string $email = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Length(max: 255)]
    private ?string $name = null;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    #[Assert\Length(min: 8, minMessage: 'A senha deve ter pelo menos {{ limit }} caracteres.')]
    private ?string $plainPassword = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?Tenant $tenant = null;

    #[ORM\Column(nullable: true, enumType: WorkGroup::class)]
    private ?WorkGroup $workGroup = WorkGroup::ADMIN;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTime $updatedAt = null;

    #[ORM\Column(length: 100, nullable: true, unique: true)]
    private ?string $resetPasswordToken = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTime $resetPasswordExpiresAt = null;

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

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): static
    {
        $this->username = $username;
        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;
        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): static
    {
        $this->name = $name;
        return $this;
    }

    public function getUserIdentifier(): string
    {
        return (string) $this->username;
    }

    /**
     * @return list<string>
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        $roles[] = 'ROLE_USER';

        if ($this->tenant === null) {
            $roles[] = 'ROLE_SUPER_ADMIN';
        }

        if ($this->workGroup !== null) {
            $roles[] = $this->workGroup->role();
        }

        return array_unique($roles);
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;
        return $this;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;
        return $this;
    }

    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }

    public function setPlainPassword(?string $plainPassword): static
    {
        $this->plainPassword = $plainPassword;
        return $this;
    }

    public function getTenant(): ?Tenant
    {
        return $this->tenant;
    }

    public function setTenant(?Tenant $tenant): static
    {
        $this->tenant = $tenant;
        return $this;
    }

    public function isSuperAdmin(): bool
    {
        return $this->tenant === null;
    }

    public function getWorkGroup(): ?WorkGroup
    {
        return $this->workGroup;
    }

    public function setWorkGroup(?WorkGroup $workGroup): static
    {
        $this->workGroup = $workGroup;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?\DateTime
    {
        return $this->updatedAt;
    }

    public function getDisplayName(): string
    {
        return $this->name ?? $this->username ?? '';
    }

    public function getResetPasswordToken(): ?string
    {
        return $this->resetPasswordToken;
    }

    public function setResetPasswordToken(?string $token): static
    {
        $this->resetPasswordToken = $token;
        return $this;
    }

    public function getResetPasswordExpiresAt(): ?\DateTime
    {
        return $this->resetPasswordExpiresAt;
    }

    public function setResetPasswordExpiresAt(?\DateTime $expiresAt): static
    {
        $this->resetPasswordExpiresAt = $expiresAt;
        return $this;
    }

    public function isResetPasswordTokenValid(): bool
    {
        return $this->resetPasswordToken !== null
            && $this->resetPasswordExpiresAt !== null
            && $this->resetPasswordExpiresAt > new \DateTime();
    }

    public function eraseCredentials(): void
    {
        $this->plainPassword = null;
    }
}
