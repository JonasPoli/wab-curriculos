<?php

namespace App\Entity;

use App\Repository\CareerRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CareerRepository::class)]
class Career
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'careers')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?AreaOfInterest $area = null;

    #[ORM\Column(length: 150)]
    #[Assert\NotBlank(message: 'O título do cargo é obrigatório.')]
    #[Assert\Length(max: 150)]
    private ?string $title = null;

    #[ORM\Column(options: ['default' => true])]
    private bool $active = true;

    #[ORM\Column(options: ['default' => 0])]
    private int $position = 0;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getArea(): ?AreaOfInterest
    {
        return $this->area;
    }

    public function setArea(?AreaOfInterest $area): static
    {
        $this->area = $area;
        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;
        return $this;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function setActive(bool $active): static
    {
        $this->active = $active;
        return $this;
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function setPosition(int $position): static
    {
        $this->position = $position;
        return $this;
    }

    public function __toString(): string
    {
        return $this->title ?? '';
    }
}
