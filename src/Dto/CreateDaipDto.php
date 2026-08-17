<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * DTO de saisie pour la creation d'un compte DAIP : evite de binder le
 * formulaire directement sur l'entite User (mass-assignment).
 */
class CreateDaipDto
{
    #[Assert\NotBlank(message: "L'email est requis.")]
    #[Assert\Email(message: "Cet email n'est pas valide.")]
    public ?string $email = null;

    public ?string $plainPassword = null;
}
