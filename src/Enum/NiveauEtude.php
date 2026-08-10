<?php

namespace App\Enum;

enum NiveauEtude: string
{
    // Primaire
    case CM2 = 'CM2';

    // Collège
    case SIXIEME = '6ème';
    case CINQUIEME = '5ème';
    case QUATRIEME = '4ème';
    case TROISIEME = '3ème';

    // Lycée
    case SECONDE = '2nde';
    case PREMIERE = '1ère';
    case TERMINALE = 'Terminale';

    // Supérieur
    case BAC_PLUS_1 = 'Bac +1';
    case BAC_PLUS_2 = 'Bac +2';
    case BAC_PLUS_3 = 'Bac +3';
    case BAC_PLUS_4 = 'Bac +4';
    case BAC_PLUS_5 = 'Bac +5';
    case BAC_PLUS_6 = 'Bac +6';
    case BAC_PLUS_7 = 'Bac +7';
    case BAC_PLUS_8 = 'Bac +8';

    public function label(): string
    {
        return $this->value;
    }
}
