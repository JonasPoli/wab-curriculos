<?php

namespace App\Enum;

enum ExampleEnum: int 
{
    case ACTIVE = 1;
    case INACTIVE = 0;

    public function label(): string
    {
        return match ($this) {
            self::ACTIVE => 'Ativo',
            self::INACTIVE => 'Inativo',
        };
    }
}