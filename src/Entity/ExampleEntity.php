<?php

namespace App\Entity;

use App\Enum\ExampleEnum;
use App\Repository\ExampleEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ExampleEntityRepository::class)]
class ExampleEntity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $name = null;

    #[ORM\Column]
    private ?bool $someBool = null;

    #[ORM\ManyToOne]
    private ?User $user = null;

    #[ORM\Column(nullable: true)]
    private ?array $someList = null;

    /**
     * @var Collection<int, User>
     */
    #[ORM\ManyToMany(targetEntity: User::class)]
    private Collection $people;

    #[ORM\Column(type: Types::DATE_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $someDate = null;

    #[ORM\Column(type: Types::TIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $sometime = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $someDatetime = null;

    #[ORM\Column(enumType: ExampleEnum::class)]
    private ?ExampleEnum $status = null;

    public function __construct()
    {
        $this->people = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function isSomeBool(): ?bool
    {
        return $this->someBool;
    }

    public function setSomeBool(bool $someBool): static
    {
        $this->someBool = $someBool;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getSomeList(): ?array
    {
        return $this->someList;
    }

    public function setSomeList(?array $someList): static
    {
        $this->someList = $someList;

        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getPeople(): Collection
    {
        return $this->people;
    }

    public function addPerson(User $person): static
    {
        if (!$this->people->contains($person)) {
            $this->people->add($person);
        }

        return $this;
    }

    public function removePerson(User $person): static
    {
        $this->people->removeElement($person);

        return $this;
    }

    public function getSomeDate(): ?\DateTimeImmutable
    {
        return $this->someDate;
    }

    public function setSomeDate(?\DateTimeImmutable $someDate): static
    {
        $this->someDate = $someDate;

        return $this;
    }

    public function getSometime(): ?\DateTimeImmutable
    {
        return $this->sometime;
    }

    public function setSometime(?\DateTimeImmutable $sometime): static
    {
        $this->sometime = $sometime;

        return $this;
    }

    public function getSomeDatetime(): ?\DateTimeImmutable
    {
        return $this->someDatetime;
    }

    public function setSomeDatetime(?\DateTimeImmutable $someDatetime): static
    {
        $this->someDatetime = $someDatetime;

        return $this;
    }

    public function getStatus(): ?ExampleEnum
    {
        return $this->status;
    }

    public function setStatus(ExampleEnum $status): static
    {
        $this->status = $status;

        return $this;
    }

}
