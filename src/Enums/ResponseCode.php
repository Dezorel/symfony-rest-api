<?php

namespace App\Enums;

enum ResponseCode: int
{
    case SUCCESS = 1000;
    case CREATED = 1001;

    case SYSTEM_ERROR = 1400;

    case NOT_FOUND = 1404;
    case MISSING_PARAMS = 1405;
    case VALIDATION_FAIL = 1406;

    public function getMessage(): string
    {
        return match ($this)
        {
            ResponseCode::SUCCESS => 'Success',
            ResponseCode::CREATED => 'Created',
            ResponseCode::SYSTEM_ERROR => 'Internal system error',
            ResponseCode::NOT_FOUND => 'Content not found',
            ResponseCode::MISSING_PARAMS => 'Missing query params',
            ResponseCode::VALIDATION_FAIL => 'Invalid param',
        };
    }
}