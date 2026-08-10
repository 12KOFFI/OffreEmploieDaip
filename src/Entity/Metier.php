<?php

namespace App\Entity;

use App\Repository\MetierRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MetierRepository::class)]
class Metier
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 150, unique: true)]
    private ?string $nom = null;

    #[ORM\Column]
    private bool $actif = true;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    /**
     * @var Collection<int, OffreMetier>
     */
    #[ORM\OneToMany(targetEntity: OffreMetier::class, mappedBy: 'metier')]
    private Collection $offreMetiers;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->offreMetiers = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;
        return $this;
    }

    public function isActif(): bool
    {
        return $this->actif;
    }

    public function setActif(bool $actif): static
    {
        $this->actif = $actif;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    /**
     * @return Collection<int, OffreMetier>
     */
    public function getOffreMetiers(): Collection
    {
        return $this->offreMetiers;
    }

    public function addOffreMetier(OffreMetier $offreMetier): static
    {
        if (!$this->offreMetiers->contains($offreMetier)) {
            $this->offreMetiers->add($offreMetier);
            $offreMetier->setMetier($this);
        }
        return $this;
    }

    public function removeOffreMetier(OffreMetier $offreMetier): static
    {
        if ($this->offreMetiers->removeElement($offreMetier)) {
            if ($offreMetier->getMetier() === $this) {
                $offreMetier->setMetier(null);
            }
        }
        return $this;
    }

    public function __toString(): string
    {
        return $this->nom ?? '';
    }
}
