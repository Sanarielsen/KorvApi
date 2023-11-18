<?php

namespace App\Shared;



use Symfony\Component\HttpFoundation\JsonResponse;

class ResponseMessage
{
    public function makeResponsePostMessage(string $code, string $message): mixed
    {
        return new JsonResponse(['status' => $code, 'message' => $message], $code, ['Content-Type'=>'application/json; charset=utf-8']);
    }
}