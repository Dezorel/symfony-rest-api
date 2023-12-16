<?php

namespace App\Controller\Api\v1;

use App\Enums\ResponseCode;

class ReponseController
{
    public static function generateFailedResponse(ResponseCode $errorCode): array
    {
        return [
            'error_code' => $errorCode->value,
            'error_message' => $errorCode->getMessage()
        ];
    }

    public static function generateSuccessResponse(ResponseCode $code): array
    {
        return [
            'code' => $code->value,
            'message' => $code->getMessage()
        ];
    }

    public static function generateSuccessResponseWithData(ResponseCode $code, array $data): array
    {
        return [
            'code' => $code->value,
            'message' => $code->getMessage(),
            'data' => $data
        ];
    }
}