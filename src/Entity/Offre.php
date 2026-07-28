<?php

namespace App\Entity;

use App\Enum\StatutOffre;
use App\Enum\TypeContrat;
use App\Repository\OffreRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: OffreRepository::class)]
class Offre
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'offres', targetEntity: Entreprise::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Entreprise $entreprise = null;

    #[ORM\ManyToOne(inversedBy: 'offres', targetEntity: Secteur::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?Secteur $secteur = null;

    #[ORM\Column(length: 255)]
    private ?string $titre = null;

    #[ORM\Column(type: 'text')]
    private ?string $description = null;

    #[ORM\Column(length: 20, enumType: TypeContrat::class)]
    private ?TypeContrat $typeContrat = null;

    #[ORM\Column(length: 100)]
    private ?string $ville = null;

    #[ORM\Column(nullable: true)]
    private ?int $salaireMin = null;

    #[ORM\Column(nullable: true)]
    private ?int $salaireMax = null;

    #[ORM\Column]
    private int $nbAnneesExperience = 0;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $niveauEtude = null;

    #[ORM\Column(length: 20, enumType: StatutOffre::class)]
    private StatutOffre $statut = StatutOffre::BROUILLON;

    #[ORM\Column]
    private ?\DateTimeImmutable $datePublication = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $dateExpiration = null;

    /**
     * @var Collection<int, Competence>
     */
    #[ORM\ManyToMany(targetEntity: Competence::class, inversedBy: 'offres')]
    #[ORM\JoinTable(name: 'offre_competence')]
    private Collection $competences;

    public function __construct()
    {
        $this->datePublication = new \DateTimeImmutable();
        $this->competences = new ArrayCollection();
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

    public function getSecteur(): ?Secteur
    {
        return $this->secteur;
    }

    public function setSecteur(?Secteur $secteur): static
    {
        $this->secteur = $secteur;
        return $this;
    }

    public function getTitre(): ?string
    {
        return $this->titre;
    }

    public function setTitre(string $titre): static
    {
        $this->titre = $titre;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;
        return $this;
    }

    public function getTypeContrat(): ?TypeContrat
    {
        return $this->typeContrat;
    }

    public function setTypeContrat(TypeContrat $typeContrat): static
    {
        $this->typeContrat = $typeContrat;
        return $this;
    }

    public function getVille(): ?string
    {
        return $this->ville;
    }

    public function setVille(string $ville): static
    {
        $this->ville = $ville;
        return $this;
    }

    public function getSalaireMin(): ?int
    {
        return $this->salaireMin;
    }

    public function setSalaireMin(?int $salaireMin): static
    {
        $this->salaireMin = $salaireMin;
        return $this;
    }

    public function getSalaireMax(): ?int
    {
        return $this->salaireMax;
    }

    public function setSalaireMax(?int $salaireMax): static
    {
        $this->salaireMax = $salaireMax;
        return $this;
    }

    public function getNbAnneesExperience(): int
    {
        return $this->nbAnneesExperience;
    }

    public function setNbAnneesExperience(int $nbAnneesExperience): static
    {
        $this->nbAnneesExperience = $nbAnneesExperience;
        return $this;
    }

    public function getNiveauEtude(): ?string
    {
        return $this->niveauEtude;
    }

    public function setNiveauEtude(?string $niveauEtude): static
    {
        $this->niveauEtude = $niveauEtude;
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

    public function setDatePublication(\DateTimeImmutable $datePublication): static
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
     * @return Collection<int, Competence>
     */
    public function getCompetences(): Collection
    {
        return $this->competences;
    }

    public function addCompetence(Competence $competence): static
    {
        if (!$this->competences->contains($competence)) {
            $this->competences->add($competence);
        }
        return $this;
    }

    public function removeCompetence(Competence $competence): static
    {
        $this->competences->removeElement($competence);
        return $this;
    }

    public function __toString(): string
    {
        return $this->titre ?? '';
    }
}
