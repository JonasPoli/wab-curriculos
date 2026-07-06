<?php

namespace App\Entity;

use App\Repository\TenantRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

#[ORM\Entity(repositoryClass: TenantRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[Vich\Uploadable]
class Tenant
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, unique: true)]
    #[Assert\NotBlank(message: 'O domínio é obrigatório.')]
    private string $domain = '';

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'O nome é obrigatório.')]
    private string $name = '';

    #[Vich\UploadableField(mapping: 'tenant_logo', fileNameProperty: 'logo')]
    private ?File $logoFile = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $logo = null;

    #[Vich\UploadableField(mapping: 'tenant_dark_logo', fileNameProperty: 'darkLogo')]
    private ?File $darkLogoFile = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $darkLogo = null;

    #[Vich\UploadableField(mapping: 'tenant_favicon', fileNameProperty: 'favicon')]
    private ?File $faviconFile = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $favicon = null;

    #[ORM\Column(length: 7, nullable: true, options: ['default' => '#0044cc'])]
    private ?string $primaryColor = '#0044cc';

    #[ORM\Column(length: 7, nullable: true, options: ['default' => '#ffaa00'])]
    private ?string $secondaryColor = '#ffaa00';

    #[ORM\Column(length: 7, nullable: true, options: ['default' => '#3b82f6'])]
    private ?string $primaryColorDark = '#3b82f6';

    #[ORM\Column(length: 7, nullable: true, options: ['default' => '#fbbf24'])]
    private ?string $secondaryColorDark = '#fbbf24';

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Email(message: 'Informe um e-mail de contato válido.')]
    private ?string $contactEmail = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $phone = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $address = null;

    #[ORM\Column(length: 50, nullable: true, options: ['default' => 'moderno'])]
    private ?string $activeTheme = 'moderno';

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $seoTitle = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $seoDescription = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $recaptchaSiteKey = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $recaptchaSecretKey = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $lgpdTermsHtml = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $privacyPolicyHtml = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $heroTitle = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $heroSubtitle = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $heroDescription = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $ctaText = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $ctaSubtext = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $headerScript = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $bodyScript = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTime $updatedAt = null;

    // ─── Lifecycle Callbacks ──────────────────────────────────────────────────

    #[ORM\PrePersist]
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

    public function getDomain(): string
    {
        return $this->domain;
    }

    public function setDomain(string $domain): static
    {
        $this->domain = $domain;
        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;
        return $this;
    }

    public function getLogoFile(): ?File
    {
        return $this->logoFile;
    }

    public function setLogoFile(?File $logoFile): static
    {
        $this->logoFile = $logoFile;
        if ($logoFile !== null) {
            $this->updatedAt = new \DateTime();
        }
        return $this;
    }

    public function getLogo(): ?string
    {
        return $this->logo;
    }

    public function setLogo(?string $logo): static
    {
        $this->logo = $logo;
        return $this;
    }

    public function getDarkLogoFile(): ?File
    {
        return $this->darkLogoFile;
    }

    public function setDarkLogoFile(?File $darkLogoFile): static
    {
        $this->darkLogoFile = $darkLogoFile;
        if ($darkLogoFile !== null) {
            $this->updatedAt = new \DateTime();
        }
        return $this;
    }

    public function getDarkLogo(): ?string
    {
        return $this->darkLogo;
    }

    public function setDarkLogo(?string $darkLogo): static
    {
        $this->darkLogo = $darkLogo;
        return $this;
    }

    public function getFaviconFile(): ?File
    {
        return $this->faviconFile;
    }

    public function setFaviconFile(?File $faviconFile): static
    {
        $this->faviconFile = $faviconFile;
        if ($faviconFile !== null) {
            $this->updatedAt = new \DateTime();
        }
        return $this;
    }

    public function getFavicon(): ?string
    {
        return $this->favicon;
    }

    public function setFavicon(?string $favicon): static
    {
        $this->favicon = $favicon;
        return $this;
    }

    public function getPrimaryColor(): ?string
    {
        return $this->primaryColor;
    }

    public function setPrimaryColor(?string $primaryColor): static
    {
        $this->primaryColor = $primaryColor;
        return $this;
    }

    public function getSecondaryColor(): ?string
    {
        return $this->secondaryColor;
    }

    public function setSecondaryColor(?string $secondaryColor): static
    {
        $this->secondaryColor = $secondaryColor;
        return $this;
    }

    public function getPrimaryColorDark(): ?string
    {
        return $this->primaryColorDark;
    }

    public function setPrimaryColorDark(?string $primaryColorDark): static
    {
        $this->primaryColorDark = $primaryColorDark;
        return $this;
    }

    public function getSecondaryColorDark(): ?string
    {
        return $this->secondaryColorDark;
    }

    public function setSecondaryColorDark(?string $secondaryColorDark): static
    {
        $this->secondaryColorDark = $secondaryColorDark;
        return $this;
    }

    public function getContactEmail(): ?string
    {
        return $this->contactEmail;
    }

    public function setContactEmail(?string $contactEmail): static
    {
        $this->contactEmail = $contactEmail;
        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): static
    {
        $this->phone = $phone;
        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(?string $address): static
    {
        $this->address = $address;
        return $this;
    }

    public function getActiveTheme(): ?string
    {
        return $this->activeTheme;
    }

    public function setActiveTheme(?string $activeTheme): static
    {
        $this->activeTheme = $activeTheme;
        return $this;
    }

    public function getSeoTitle(): ?string
    {
        return $this->seoTitle;
    }

    public function setSeoTitle(?string $seoTitle): static
    {
        $this->seoTitle = $seoTitle;
        return $this;
    }

    public function getSeoDescription(): ?string
    {
        return $this->seoDescription;
    }

    public function setSeoDescription(?string $seoDescription): static
    {
        $this->seoDescription = $seoDescription;
        return $this;
    }

    public function getRecaptchaSiteKey(): ?string
    {
        return $this->recaptchaSiteKey;
    }

    public function setRecaptchaSiteKey(?string $recaptchaSiteKey): static
    {
        $this->recaptchaSiteKey = $recaptchaSiteKey;
        return $this;
    }

    public function getRecaptchaSecretKey(): ?string
    {
        return $this->recaptchaSecretKey;
    }

    public function setRecaptchaSecretKey(?string $recaptchaSecretKey): static
    {
        $this->recaptchaSecretKey = $recaptchaSecretKey;
        return $this;
    }

    public function getLgpdTermsHtml(): ?string
    {
        return $this->lgpdTermsHtml;
    }

    public function setLgpdTermsHtml(?string $lgpdTermsHtml): static
    {
        $this->lgpdTermsHtml = $lgpdTermsHtml;
        return $this;
    }

    public function getPrivacyPolicyHtml(): ?string
    {
        return $this->privacyPolicyHtml;
    }

    public function setPrivacyPolicyHtml(?string $privacyPolicyHtml): static
    {
        $this->privacyPolicyHtml = $privacyPolicyHtml;
        return $this;
    }

    public function getHeroTitle(): ?string { return $this->heroTitle; }
    public function setHeroTitle(?string $heroTitle): static { $this->heroTitle = $heroTitle; return $this; }

    public function getHeroSubtitle(): ?string { return $this->heroSubtitle; }
    public function setHeroSubtitle(?string $heroSubtitle): static { $this->heroSubtitle = $heroSubtitle; return $this; }

    public function getHeroDescription(): ?string { return $this->heroDescription; }
    public function setHeroDescription(?string $heroDescription): static { $this->heroDescription = $heroDescription; return $this; }

    public function getCtaText(): ?string { return $this->ctaText; }
    public function setCtaText(?string $ctaText): static { $this->ctaText = $ctaText; return $this; }

    public function getCtaSubtext(): ?string { return $this->ctaSubtext; }
    public function setCtaSubtext(?string $ctaSubtext): static { $this->ctaSubtext = $ctaSubtext; return $this; }

    public function getHeaderScript(): ?string { return $this->headerScript; }
    public function setHeaderScript(?string $headerScript): static { $this->headerScript = $headerScript; return $this; }

    public function getBodyScript(): ?string { return $this->bodyScript; }
    public function setBodyScript(?string $bodyScript): static { $this->bodyScript = $bodyScript; return $this; }

    public function getUpdatedAt(): ?\DateTime
    {
        return $this->updatedAt;
    }

    public function __toString(): string
    {
        return $this->name ?: $this->domain;
    }
}
