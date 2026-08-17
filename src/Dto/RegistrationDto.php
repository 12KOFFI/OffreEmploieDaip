<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * DTO de saisie pour l'inscription d'une entreprise : evite de binder le
 * formulaire directement sur l'entite User/Entreprise (mass-assignment).
 */
class RegistrationDto
{
    #[Assert\NotBlank(message: "L'email est requis.")]
    #[Assert\Email(message: "Cet email n'est pas valide.")]
    public ?string $email = null;

    public ?string $plainPassword = null;

    #[Assert\NotBlank(message: "Le nom de l'entreprise est obligatoire.")]
    public ?string $nom = null;

    public ?string $contact = null;

    public ?string $contactResponsable = null;

    public ?string $autreContact = null;

    public ?string $description = null;
}
