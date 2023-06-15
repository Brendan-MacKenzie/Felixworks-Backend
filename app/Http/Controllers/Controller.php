<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    public function failedValidationResponse(\Illuminate\Validation\Validator $validator)
    {
        return response()->json([
            'status' => 'fail',
            'message' => 'There was a problem with the provided input',
            'issue' => $validator->failed(),
            'errors' => $validator->errors(),
        ], 400);
    }

    public function failedExceptionResponse(\Exception $exception)
    {
        $code = (is_int($exception->getCode()) && $exception->getCode() > 0) ? $exception->getCode() : 500;

        $response = response()->json([
            'status' => 'fail',
            'message' => $exception->getMessage(),
        ], $code);

        return $response;
    }

    public function internalErrorResponse(string $message)
    {
        return response()->json([
            'status' => 'fail',
            'message' => $message,
        ], 500);
    }

    public function successResponse($data, $extra = null)
    {
        $json = [
            'status' => 'success',
            'data' => $data,
        ];

        if ($extra) {
            $json['extra'] = $extra;
        }

        return response()->json($json, 200);
    }

    public function messageResponse($message)
    {
        return response()->json([
            'status' => 'success',
            'message' => $message,
        ], 200);
    }
}
