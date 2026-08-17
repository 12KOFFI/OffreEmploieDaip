<?php

namespace App\Entity;

use App\Enum\StatutOffre;
use App\Repository\OffreRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

#[ORM\Entity(repositoryClass: OffreRepository::class)]
#[ORM\Index(name: 'idx_offre_statut', columns: ['statut'])]
#[ORM\Index(name: 'idx_offre_date_publication', columns: ['date_publication'])]
#[ORM\Index(name: 'idx_offre_date_expiration', columns: ['date_expiration'])]
#[Assert\Callback('validateTitrePourPublication')]
class Offre
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'offres', targetEntity: Entreprise::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Entreprise $entreprise = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $titre = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $image = null;

    #[ORM\Column]
    private int $views = 0;

    #[ORM\Column(length: 20, enumType: StatutOffre::class)]
    private StatutOffre $statut = StatutOffre::BROUILLON;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $datePublication = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $dateExpiration = null;

    /**
     * @var Collection<int, OffreMetier>
     */
    #[ORM\OneToMany(
        targetEntity: OffreMetier::class,
        mappedBy: 'offre',
        cascade: ['persist', 'remove'],
        orphanRemoval: true,
    )]
    private Collection $offreMetiers;

    public function __construct()
    {
        $this->offreMetiers = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEntreprise(): ?Entreprise
    {
        return $this->entreprise;
    }

    public function setEntreprise(?Entreprise $entreprise): static
    {
        $this->entreprise = $entreprise;
        return $this;
    }

    public function getTitre(): ?string
    {
        return $this->titre;
    }

    public function setTitre(?string $titre): static
    {
        $this->titre = $titre;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;
        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): static
    {
        $this->image = $image;
        return $this;
    }

    public function getViews(): int
    {
        return $this->views;
    }

    public function setViews(int $views): static
    {
        $this->views = $views;
        return $this;
    }

    public function incrementViews(): static
    {
        $this->views++;
        return $this;
    }

    public function getStatut(): StatutOffre
    {
        return $this->statut;
    }

    public function setStatut(StatutOffre $statut): static
    {
        $this->statut = $statut;
        return $this;
    }

    public function getDatePublication(): ?\DateTimeImmutable
    {
        return $this->datePublication;
    }

    public function setDatePublication(?\DateTimeImmutable $datePublication): static
    {
        $this->datePublication = $datePublication;
        return $this;
    }

    public function getDateExpiration(): ?\DateTimeImmutable
    {
        return $this->dateExpiration;
    }

    public function setDateExpiration(?\DateTimeImmutable $dateExpiration): static
    {
        $this->dateExpiration = $dateExpiration;
        return $this;
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
            $offreMetier->setOffre($this);
        }

        return $this;
    }

    public function removeOffreMetier(OffreMetier $offreMetier): static
    {
        if ($this->offreMetiers->removeElement($offreMetier)) {
            if ($offreMetier->getOffre() === $this) {
                $offreMetier->setOffre(null);
            }
        }

        return $this;
    }

    public function getVilles(): array
    {
        $villes = [];
        foreach ($this->getOffreMetiers() as $om) {
            $ville = $om->getVille();
            if ($ville && !in_array($ville, $villes, true)) {
                $villes[] = $ville;
            }
        }
        return $villes;
    }

    public function getSalaireMinGlobal(): ?int
    {
        $min = null;
        foreach ($this->getOffreMetiers() as $om) {
            if ($om->getSalaireMin() !== null) {
                if ($min === null || $om->getSalaireMin() < $min) {
                    $min = $om->getSalaireMin();
                }
            }
        }
        return $min;
    }

    public function getSalaireMaxGlobal(): ?int
    {
        $max = null;
        foreach ($this->getOffreMetiers() as $om) {
            if ($om->getSalaireMax() !== null) {
                if ($max === null || $om->getSalaireMax() > $max) {
                    $max = $om->getSalaireMax();
                }
            }
        }
        return $max;
    }

    public function getTotalPostes(): int
    {
        $total = 0;
        foreach ($this->getOffreMetiers() as $om) {
            $total += $om->getNombrePostes();
        }
        return $total;
    }

    public function __toString(): string
    {
        return $this->titre ?? '';
    }

    /**
     * Une offre ne peut pas être publiée sans titre (brouillon = seul statut tolérant un titre vide).
     */
    public function validateTitrePourPublication(ExecutionContextInterface $context): void
    {
        if ($this->statut !== StatutOffre::BROUILLON && ($this->titre === null || trim($this->titre) === '')) {
            $context->buildViolation('Le titre est obligatoire dès que l\'offre n\'est plus en brouillon.')
                ->atPath('titre')
                ->addViolation();
        }
    }
}
