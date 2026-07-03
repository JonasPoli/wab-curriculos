<?php

namespace App\Entity;

use App\Contract\TenantAwareInterface;
use App\Repository\AreaOfInterestRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: AreaOfInterestRepository::class)]
class AreaOfInterest implements TenantAwareInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Tenant $tenant = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message: 'O título da área é obrigatório.')]
    #[Assert\Length(max: 100)]
    private ?string $title = null;

    #[ORM\Column(options: ['default' => 0])]
    private int $position = 0;

    /**
     * @var Collection<int, Career>
     */
    #[ORM\OneToMany(targetEntity: Career::class, mappedBy: 'area', cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[ORM\OrderBy(['position' => 'ASC', 'title' => 'ASC'])]
    private Collection $careers;

    public function __construct()
    {
        $this->careers = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;
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

    /**
     * @return Collection<int, Career>
     */
    public function getCareers(): Collection
    {
        return $this->careers;
    }

    public function addCareer(Career $career): static
    {
        if (!$this->careers->contains($career)) {
            $this->careers->add($career);
            $career->setArea($this);
        }
        return $this;
    }

    public function removeCareer(Career $career): static
    {
        if ($this->careers->removeElement($career)) {
            if ($career->getArea() === $this) {
                $career->setArea(null);
            }
        }
        return $this;
    }

    public function __toString(): string
    {
        return $this->title ?? '';
    }
}
