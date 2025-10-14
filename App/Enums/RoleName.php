<?php

namespace App\Enums;

enum RoleName: string
{
    case ADMIN = 'ADMIN';
    case SECRETAIRE = 'SECRETAIRE';
    case STAFF = 'STAFF';
    case PATIENT = 'PATIENT';

    public function label(): string
    {
        return match ($this) {
            self::ADMIN => 'Administrateur',
            self::SECRETAIRE => 'Secrétaire',
            self::STAFF => 'Personnel médical',
            self::PATIENT => 'Patient',
        };
    }
}
