<?php

namespace App\Http\Middleware;

use Closure;
use Exception;
use App\Models\Agency;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

class AuthenticateClient
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $clientId = $request->cookie('client_id', $request->header('X-Client-Id'));
        $apiKey = $request->cookie('api_key', $request->header('Authorization'));
        $ipAddress = $request->ip();

        if (!$clientId || !$apiKey || !$ipAddress) {
            return $this->failedExceptionResponse(new Exception('Credentials not provided or incomplete.', 401));
        }

        try {
            $this->validate($clientId, $apiKey, $ipAddress);
        } catch (Exception $exception) {
            return $this->failedExceptionResponse($exception);
        }

        return $next($request);
    }

    public function validate($clientId, $apiKey, $ipAddress)
    {
        $agency = Agency::where('id', $clientId)
            ->where('api_key', Crypt::encrypt($apiKey))
            ->where('ip_address', Crypt::encrypt($ipAddress))
            ->first();

        if (!$agency) {
            throw new Exception('Agency credentials are not correct.', 403);
        }

        return true;
    }

    private function failedExceptionResponse(\Exception $exception)
    {
        $code = (is_int($exception->getCode()) && $exception->getCode() > 0) ? $exception->getCode() : 500;

        return response()->json([
            'status' => 'fail',
            'message' => $exception->getMessage(),
        ], $code);
    }
}
