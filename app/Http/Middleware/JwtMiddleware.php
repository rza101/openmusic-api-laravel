<?php

namespace App\Http\Middleware;

use App\Models\User;
use App\Services\JwtService;
use Closure;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class JwtMiddleware
{
    private $jwtService;

    public function __construct()
    {
        $this->jwtService = new JwtService();
    }

    public function handle(Request $request, Closure $next): Response
    {
        try {
            $accessToken = $request->bearerToken();

            if (!$accessToken) {
                return response()->json([
                    'status' => 'fail',
                    'message' => 'Bearer token not provided'
                ], 401);
            }

            if (!$this->jwtService->validateAccessToken($accessToken)) {
                return response()->json([
                    'status' => 'fail',
                    'message' => 'Invalid or expired bearer token'
                ], 401);
            }

            $accessTokenData = $this->jwtService->parseToken($accessToken);
            $user = User::find($accessTokenData->claims()->get('userId'));

            if ($user) {
                Auth::setUser($user);
                return $next($request);
            } else {
                throw new Exception('User not found');
            }
        } catch (Exception $e) {
            return response()->json([
                'status' => 'fail',
                'message' => 'Authentication failed'
            ], 401);
        }
    }
}
