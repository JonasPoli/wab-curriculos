<?php

namespace App\Enum;

enum WorkGroup: int
{
    case ADMIN   = 0;
    case EDITOR  = 1;
    case REVISOR = 2;

    public function label(): string
    {
        return match($this) {
            self::ADMIN   => 'Administrador',
            self::EDITOR  => 'Editor / Recrutador',
            self::REVISOR => 'Revisor',
        };
    }

    public function role(): string
    {
        return match($this) {
            self::ADMIN   => 'ROLE_ADMIN',
            self::EDITOR  => 'ROLE_EDITOR',
            self::REVISOR => 'ROLE_REVISOR',
        };
    }
}
