<?php

namespace App\Enum;

enum NiveauEtude: string
{
    case CM2            = 'CM2';
    case SIXIEME        = '6ème';
    case CINQUIEME      = '5ème';
    case QUATRIEME      = '4ème';
    case TROISIEME      = '3ème';
    case BEPC           = 'BEPC';
    case SECONDE        = '2nde';
    case PREMIERE       = '1ère';
    case CAP            = 'CAP';
    case TERMINALE      = 'Terminale';
    case BAC            = 'BAC';
    case BAC_PLUS_1     = 'Bac +1';
    case BAC_PLUS_2     = 'Bac +2 (BTS / DUT)';
    case BTS            = 'BTS';
    case BAC_PLUS_3     = 'Bac +3 (Licence / Bachelor)';
    case MASTER         = 'Master';
    case BAC_PLUS_6     = 'Bac +6';
    case BAC_PLUS_7     = 'Bac +7';
    case BAC_PLUS_8     = 'Doctorat';

    public function label(): string
    {
        return $this->value;
    }
}
