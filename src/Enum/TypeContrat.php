<?php

namespace App\Enum;

enum TypeContrat: string
{
    case CDI = 'CDI';
    case CDD = 'CDD';
    case STAGE = 'Stage';
    case ALTERNANCE = 'Alternance';
    case FREELANCE = 'Freelance';

    public function label(): string
    {
        return $this->value;
    }
}
