<?php

namespace App\Entity;

use App\Repository\JournalActiviteRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: JournalActiviteRepository::class)]
class JournalActivite
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $action = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $cibleType = null;

    #[ORM\Column(nullable: true)]
    private ?int $cibleId = null;

    #[ORM\Column(length: 180, nullable: true)]
    private ?string $utilisateurEmail = null;

    #[ORM\Column(length: 45, nullable: true)]
    private ?string $adresseIp = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $date = null;

    public function __construct()
    {
        $this->date = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAction(): ?string
    {
        return $this->action;
    }

    public function setAction(?string $action): static
    {
        $this->action = $action;

        return $this;
    }

    public function getCibleType(): ?string
    {
        return $this->cibleType;
    }

    public function setCibleType(?string $cibleType): static
    {
        $this->cibleType = $cibleType;

        return $this;
    }

    public function getCibleId(): ?int
    {
        return $this->cibleId;
    }

    public function setCibleId(?int $cibleId): static
    {
        $this->cibleId = $cibleId;

        return $this;
    }

    public function getUtilisateurEmail(): ?string
    {
        return $this->utilisateurEmail;
    }

    public function setUtilisateurEmail(?string $utilisateurEmail): static
    {
        $this->utilisateurEmail = $utilisateurEmail;

        return $this;
    }

    public function getAdresseIp(): ?string
    {
        return $this->adresseIp;
    }

    public function setAdresseIp(?string $adresseIp): static
    {
        $this->adresseIp = $adresseIp;

        return $this;
    }

    public function getDate(): ?\DateTimeImmutable
    {
        return $this->date;
    }

    public function setDate(\DateTimeImmutable $date): static
    {
        $this->date = $date;

        return $this;
    }
}