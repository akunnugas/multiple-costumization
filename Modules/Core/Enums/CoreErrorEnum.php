<?php

namespace Modules\Core\Enums;

use Modules\Core\Helpers\ServiceError;

enum CoreErrorEnum
{
    use ErrorEnum;

    case Duplicate;
    case Forbidden;
    case Invalid;
    case NotFound;
    case Referenced;

    case NonaktifPeriodeAktif;

    function id()
    {
        return match ($this) {
            static::Forbidden => ServiceError::ERROR_FORBIDDEN,
            static::NotFound => ServiceError::ERROR_NOT_FOUND,
            static::Duplicate => ServiceError::ERROR_CONFLICT,
            static::Invalid => ServiceError::ERROR_VALIDATION,
            static::Referenced => ServiceError::ERROR_DEFAULT,

            static::NonaktifPeriodeAktif => ServiceError::ERROR_DEFAULT,
        };
    }
}

