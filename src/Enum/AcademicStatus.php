<?php

namespace App\Enum;

enum AcademicStatus: string
{
    case CONCLUIDO    = 'Concluído';
    case INCOMPLETO   = 'Incompleto';
    case EM_ANDAMENTO = 'Em andamento';

    public function label(): string
    {
        return $this->value;
    }
}
