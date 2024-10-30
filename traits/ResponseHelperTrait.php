<?php

namespace app\traits;

use Yii;

trait ResponseHelperTrait
{
    private function createResponse(array $data): array
    {
        return [
            'status' => 'success',
            'data' => $data,
        ];
    }

    private function createErrorResponse(string $message, int $code): array
    {
        Yii::$app->response->statusCode = $code;
        return [
            'status' => 'error',
            'message' => $message,
        ];
    }
}