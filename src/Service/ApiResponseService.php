<?php

declare(strict_types=1);

namespace App\Service;

use Symfony\Component\HttpFoundation\JsonResponse;

final class ApiResponseService
{
    /**
     * @param array{status: int, message: string, data?: mixed, errors?: mixed} $result
     */
    public function fromServiceResult(array $result): JsonResponse
    {
        $status = $result['status'];
        $body = ['message' => $result['message']];

        if (array_key_exists('data', $result)) {
            $body['data'] = $result['data'];
        }

        if (array_key_exists('errors', $result)) {
            $body['errors'] = $result['errors'];
        }

        return new JsonResponse($body, $status);
    }
}