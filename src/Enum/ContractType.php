<?php

namespace App\Enum;

enum ContractType: string
{
    case CLT        = 'CLT';
    case PJ         = 'PJ';
    case ESTAGIO    = 'Estágio';
    case TEMPORARIO = 'Temporário';
    case VOLUNTARIO = 'Voluntário';

    public function label(): string
    {
        return $this->value;
    }
}
