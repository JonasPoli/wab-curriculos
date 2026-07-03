<?php

namespace App\Enum;

enum EducationLevel: string
{
    case FUNDAMENTAL    = 'Ensino Fundamental';
    case MEDIO          = 'Ensino Médio';
    case TECNICO        = 'Curso Técnico';
    case SUPERIOR_INC   = 'Ensino Superior Incompleto';
    case SUPERIOR_COMP  = 'Ensino Superior Completo';
    case POS_GRADUACAO  = 'Pós-graduação';
    case MESTRADO       = 'Mestrado';
    case DOUTORADO      = 'Doutorado';

    public function label(): string
    {
        return $this->value;
    }
}
