<?php

namespace App\Entity;

use App\Enum\Diplome;
use App\Enum\NiveauEtude;
use App\Enum\TypeContrat;
use App\Repository\OffreMetierRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

#[ORM\Entity(repositoryClass: OffreMetierRepository::class)]
#[ORM\Index(name: 'idx_offre_metier_ville', columns: ['ville'])]
#[Assert\Callback('validateSalaires')]
class OffreMetier
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'offreMetiers', targetEntity: Offre::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Offre $offre = null;

    #[ORM\ManyToOne(inversedBy: 'offreMetiers', targetEntity: Metier::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Metier $metier = null;

    #[ORM\Column(length: 20, nullable: true, enumType: TypeContrat::class)]
    private ?TypeContrat $typeContrat = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message: 'La ville est obligatoire.')]
    private ?string $ville = null;

    #[ORM\Column]
    #[Assert\Positive(message: 'Le nombre de postes doit être supérieur à 0.')]
    private int $nombrePostes = 1;

    #[ORM\Column(nullable: true)]
    private ?int $nbAnneesExperience = null;

    #[ORM\Column(length: 50, nullable: true, enumType: NiveauEtude::class)]
    private ?NiveauEtude $niveauEtude = null;

    #[ORM\Column(length: 100, nullable: true, enumType: Diplome::class)]
    private ?Diplome $diplome = null;

    #[ORM\Column(nullable: true)]
    private ?int $salaireMin = null;

    #[ORM\Column(nullable: true)]
    private ?int $salaireMax = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $prerequis = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOffre(): ?Offre
    {
        return $this->offre;
    }

    public function setOffre(?Offre $offre): static
    {
        $this->offre = $offre;
        return $this;
    }

    public function getMetier(): ?Metier
    {
        return $this->metier;
    }

    public function setMetier(?Metier $metier): static
    {
        $this->metier = $metier;
        return $this;
    }

    public function getTypeContrat(): ?TypeContrat
    {
        return $this->typeContrat;
    }

    public function setTypeContrat(?TypeContrat $typeContrat): static
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

    public function getNombrePostes(): int
    {
        return $this->nombrePostes;
    }

    public function setNombrePostes(int $nombrePostes): static
    {
        $this->nombrePostes = $nombrePostes;
        return $this;
    }

    public function getNbAnneesExperience(): ?int
    {
        return $this->nbAnneesExperience;
    }

    public function setNbAnneesExperience(?int $nbAnneesExperience): static
    {
        $this->nbAnneesExperience = $nbAnneesExperience;
        return $this;
    }

    public function getNiveauEtude(): ?NiveauEtude
    {
        return $this->niveauEtude;
    }

    public function setNiveauEtude(?NiveauEtude $niveauEtude): static
    {
        $this->niveauEtude = $niveauEtude;
        return $this;
    }

    public function getDiplome(): ?Diplome
    {
        return $this->diplome;
    }

    public function setDiplome(?Diplome $diplome): static
    {
        $this->diplome = $diplome;
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

    public function getPrerequis(): ?string
    {
        return $this->prerequis;
    }

    public function setPrerequis(?string $prerequis): static
    {
        $this->prerequis = $prerequis;
        return $this;
    }

    public function validateSalaires(ExecutionContextInterface $context): void
    {
        if ($this->salaireMin !== null && $this->salaireMax !== null && $this->salaireMin > $this->salaireMax) {
            $context->buildViolation('Le salaire minimum ne peut pas \u00eatre sup\u00e9rieur au salaire maximum.')
                ->atPath('salaireMin')
                ->addViolation();
        }
    }
}
