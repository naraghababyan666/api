<?php

namespace App\Http\Traits;


use Exception;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\JsonResponse;
use JsonSerializable;
use Symfony\Component\HttpFoundation\Response;

use function response;

trait ApiResponseHelpers
{
    private ?array $_api_helpers_defaultSuccessData = ['success' => true];

    public function respondWithSuccess($contents = null): JsonResponse
    {
        $data = [] === $contents
            ? $this->_api_helpers_defaultSuccessData
            : $contents;

        return $this->apiResponse($data);
    }

    public function setDefaultSuccessResponse(?array $content = null): self
    {
        $this->_api_helpers_defaultSuccessData = $content ?? [];
        return $this;
    }

    public function respondOk(string $message): JsonResponse
    {
        return $this->respondWithSuccess(['success' => $message]);
    }

    public function respondUnAuthenticated(?string $message = null): JsonResponse
    {
        return $this->apiResponse(
            ['message' => $message ?? 'Unauthenticated'],
            Response::HTTP_UNAUTHORIZED
        );
    }

    public function respondForbidden(?string $message = null): JsonResponse
    {
        return $this->apiResponse(
            ['error' => $message ?? 'Forbidden'],
            Response::HTTP_FORBIDDEN
        );
    }

    private function apiResponse( $data,  $code = 200, $success = true): JsonResponse
    {
        return response()->json(['success' =>$success ,"data"=>$data], $code)->header('Cache-Control', 'no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
    }

}
