<?php

namespace App\Http\Middleware;

use Closure;
use Exception;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Http\Request;
use Firebase\JWT\ExpiredException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class AuthServerValidation
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
        $accessToken = $request->cookie('access_token', $request->header('Authorization'));
        $profileToken = $request->cookie('profile_token', $request->header('X-Profile-Authorization'));

        if (!$accessToken || !$profileToken) {
            return $this->failedExceptionResponse(new Exception('Token not provided.', 401));
        }

        try {
            $this->validate($accessToken);

            $profileTokenData = $this->validate($profileToken, true);

            Auth::loginUsingId($profileTokenData->sub);

            if (!Auth::user()) {
                return $this->failedExceptionResponse(new Exception('User not found.', 404));
            }
        } catch (Exception $exception) {
            return $this->failedExceptionResponse($exception);
        }

        return $next($request);
    }

    public function validate($jwtToken, $ignoreExpiration = false)
    {
        $publicKey = $this->getKey();

        try {
            $jwtData = JWT::decode($jwtToken, new Key($publicKey, 'RS256'));

            if (!$ignoreExpiration && !property_exists($jwtData, 'exp')) {
                throw new Exception();
            }
        } catch (ExpiredException $e) {
            // https://stackoverflow.com/a/41250085
            throw new Exception('Provided token is expired.', 401);
        } catch (Exception $e) {
            throw new Exception('An error while decoding token.', 500);
        }

        return $jwtData;
    }

    private function getKey($private = false)
    {
        $publicKey = Storage::get(config('authserver.public_key', 'auth-server-public.key'));

        if (!$publicKey) {
            throw new Exception('Key is missing.', 500);
        }

        return $publicKey;
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
