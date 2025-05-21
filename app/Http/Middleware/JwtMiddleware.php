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
            $accessTokenString = $request->bearerToken();

            if (!$accessTokenString) {
                return response()->json([
                    'status' => 'fail',
                    'message' => 'Token not provided'
                ], 401);
            }

            if (!$this->jwtService->validateAccessToken($accessTokenString)) {
                return response()->json([
                    'status' => 'fail',
                    'message' => 'Invalid or expired token'
                ], 401);
            }

            $accessToken = $this->jwtService->parseToken($accessTokenString);

            $userId = $accessToken->claims()->get('userId');
            $user = User::find($userId);

            if ($user) {
                Auth::setUser($user);
                return $next($request);
            } else {
                throw new Exception('User not found');
            }
        } catch (Exception $e) {
            return response()->json([
                'status' => 'fail',
                'message' => $e->getMessage()
            ], 401);
        }
    }
}
