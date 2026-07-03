<?php

namespace App\Enum;

enum LgpdActionType: string
{
    case CONSENT_GIVEN  = 'CONSENT_GIVEN';
    case DATA_EXPORTED  = 'DATA_EXPORTED';
    case DATA_DELETED   = 'DATA_DELETED';

    public function label(): string
    {
        return match($this) {
            self::CONSENT_GIVEN => 'Consentimento registrado',
            self::DATA_EXPORTED => 'Dados exportados',
            self::DATA_DELETED  => 'Dados excluídos',
        };
    }
}
