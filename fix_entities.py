import os

files = {
    "www/src/Entity/Plan.php": r"""<?php

namespace App\Entity;

use App\Repository\PlanRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PlanRepository::class)]
class Plan
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int __DOLLAR__id = null;

    #[ORM\Column(length: 255)]
    private ?string __DOLLAR__name = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string __DOLLAR__description = null;

    #[ORM\Column]
    private ?int __DOLLAR__limitGeneration = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string __DOLLAR__image = null;

    #[ORM\Column(length: 255)]
    private ?string __DOLLAR__role = null;

    #[ORM\Column]
    private ?float __DOLLAR__price = null;

    #[ORM\Column(nullable: true)]
    private ?float __DOLLAR__specialPrice = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface __DOLLAR__specialPriceFrom = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface __DOLLAR__specialPriceTo = null;

    #[ORM\Column]
    private ?bool __DOLLAR__active = null;

    #[ORM\Column]
    private ?\DateTimeImmutable __DOLLAR__createdAt = null;

    public function __construct()
    {
        __DOLLAR__this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return __DOLLAR__this->id;
    }

    public function getName(): ?string
    {
        return __DOLLAR__this->name;
    }

    public function setName(string __DOLLAR__name): static
    {
        __DOLLAR__this->name = __DOLLAR__name;

        return __DOLLAR__this;
    }

    public function getDescription(): ?string
    {
        return __DOLLAR__this->description;
    }

    public function setDescription(string __DOLLAR__description): static
    {
        __DOLLAR__this->description = __DOLLAR__description;

        return __DOLLAR__this;
    }

    public function getLimitGeneration(): ?int
    {
        return __DOLLAR__this->limitGeneration;
    }

    public function setLimitGeneration(int __DOLLAR__limitGeneration): static
    {
        __DOLLAR__this->limitGeneration = __DOLLAR__limitGeneration;

        return __DOLLAR__this;
    }

    public function getImage(): ?string
    {
        return __DOLLAR__this->image;
    }

    public function setImage(?string __DOLLAR__image): static
    {
        __DOLLAR__this->image = __DOLLAR__image;

        return __DOLLAR__this;
    }

    public function getRole(): ?string
    {
        return __DOLLAR__this->role;
    }

    public function setRole(string __DOLLAR__role): static
    {
        __DOLLAR__this->role = __DOLLAR__role;

        return __DOLLAR__this;
    }

    public function getPrice(): ?float
    {
        return __DOLLAR__this->price;
    }

    public function setPrice(float __DOLLAR__price): static
    {
        __DOLLAR__this->price = __DOLLAR__price;

        return __DOLLAR__this;
    }

    public function getSpecialPrice(): ?float
    {
        return __DOLLAR__this->specialPrice;
    }

    public function setSpecialPrice(?float __DOLLAR__specialPrice): static
    {
        __DOLLAR__this->specialPrice = __DOLLAR__specialPrice;

        return __DOLLAR__this;
    }

    public function getSpecialPriceFrom(): ?\DateTimeInterface
    {
        return __DOLLAR__this->specialPriceFrom;
    }

    public function setSpecialPriceFrom(?\DateTimeInterface __DOLLAR__specialPriceFrom): static
    {
        __DOLLAR__this->specialPriceFrom = __DOLLAR__specialPriceFrom;

        return __DOLLAR__this;
    }

    public function getSpecialPriceTo(): ?\DateTimeInterface
    {
        return __DOLLAR__this->specialPriceTo;
    }

    public function setSpecialPriceTo(?\DateTimeInterface __DOLLAR__specialPriceTo): static
    {
        __DOLLAR__this->specialPriceTo = __DOLLAR__specialPriceTo;

        return __DOLLAR__this;
    }

    public function isActive(): ?bool
    {
        return __DOLLAR__this->active;
    }

    public function setActive(bool __DOLLAR__active): static
    {
        __DOLLAR__this->active = __DOLLAR__active;

        return __DOLLAR__this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return __DOLLAR__this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable __DOLLAR__createdAt): static
    {
        __DOLLAR__this->createdAt = __DOLLAR__createdAt;

        return __DOLLAR__this;
    }
}
""",
    "www/src/Entity/Generation.php": r"""<?php

namespace App\Entity;

use App\Repository\GenerationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: GenerationRepository::class)]
class Generation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int __DOLLAR__id = null;

    #[ORM\Column(length: 255)]
    private ?string __DOLLAR__file = null;

    #[ORM\Column]
    private ?\DateTimeImmutable __DOLLAR__createdAt = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?User __DOLLAR__user = null;

    #[ORM\ManyToMany(targetEntity: UserContact::class)]
    private Collection __DOLLAR__userContacts;

    public function __construct()
    {
        __DOLLAR__this->userContacts = new ArrayCollection();
        __DOLLAR__this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return __DOLLAR__this->id;
    }

    public function getFile(): ?string
    {
        return __DOLLAR__this->file;
    }

    public function setFile(string __DOLLAR__file): static
    {
        __DOLLAR__this->file = __DOLLAR__file;

        return __DOLLAR__this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return __DOLLAR__this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable __DOLLAR__createdAt): static
    {
        __DOLLAR__this->createdAt = __DOLLAR__createdAt;

        return __DOLLAR__this;
    }

    public function getUser(): ?User
    {
        return __DOLLAR__this->user;
    }

    public function setUser(?User __DOLLAR__user): static
    {
        __DOLLAR__this->user = __DOLLAR__user;

        return __DOLLAR__this;
    }

    /**
     * @return Collection<int, UserContact>
     */
    public function getUserContacts(): Collection
    {
        return __DOLLAR__this->userContacts;
    }

    public function addUserContact(UserContact __DOLLAR__userContact): static
    {
        if (!__DOLLAR__this->userContacts->contains(__DOLLAR__userContact)) {
            __DOLLAR__this->userContacts->add(__DOLLAR__userContact);
        }

        return __DOLLAR__this;
    }

    public function removeUserContact(UserContact __DOLLAR__userContact): static
    {
        __DOLLAR__this->userContacts->removeElement(__DOLLAR__userContact);

        return __DOLLAR__this;
    }
}
""",
    "www/src/Entity/UserContact.php": r"""<?php

namespace App\Entity;

use App\Repository\UserContactRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserContactRepository::class)]
class UserContact
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int __DOLLAR__id = null;

    #[ORM\Column(length: 255)]
    private ?string __DOLLAR__lastname = null;

    #[ORM\Column(length: 255)]
    private ?string __DOLLAR__firstname = null;

    #[ORM\Column(length: 255)]
    private ?string __DOLLAR__email = null;

    #[ORM\ManyToOne(inversedBy: 'contacts')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User __DOLLAR__user = null;

    public function getId(): ?int
    {
        return __DOLLAR__this->id;
    }

    public function getLastname(): ?string
    {
        return __DOLLAR__this->lastname;
    }

    public function setLastname(string __DOLLAR__lastname): static
    {
        __DOLLAR__this->lastname = __DOLLAR__lastname;

        return __DOLLAR__this;
    }

    public function getFirstname(): ?string
    {
        return __DOLLAR__this->firstname;
    }

    public function setFirstname(string __DOLLAR__firstname): static
    {
        __DOLLAR__this->firstname = __DOLLAR__firstname;

        return __DOLLAR__this;
    }

    public function getEmail(): ?string
    {
        return __DOLLAR__this->email;
    }

    public function setEmail(string __DOLLAR__email): static
    {
        __DOLLAR__this->email = __DOLLAR__email;

        return __DOLLAR__this;
    }

    public function getUser(): ?User
    {
        return __DOLLAR__this->user;
    }

    public function setUser(?User __DOLLAR__user): static
    {
        __DOLLAR__this->user = __DOLLAR__user;

        return __DOLLAR__this;
    }
}
"""
}

for path, content in files.items():
    try:
        # Normalize path separators for Windows
        norm_path = os.path.normpath(path)
        with open(norm_path, 'w', encoding='utf-8') as f:
            f.write(content)
        print(f"Successfully wrote {norm_path}")
    except Exception as e:
        print(f"Failed to write {path}: {e}")
