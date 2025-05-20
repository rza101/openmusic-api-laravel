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
        $tokenString = $request->bearerToken();

        if (!$tokenString) {
            return response()->json(['error' => 'Token not provided'], 401);
        }

        try {
            $token = $this->jwtService->parseAccessToken($tokenString);

            if (!$this->jwtService->validateAccessToken($tokenString)) {
                return response()->json([
                    'status' => 'fail',
                    'message' => 'Invalid or expired token'
                ], 401);
            }

            $userId = $token->claims()->get('userId');
            $user = User::findOrFail($userId);

            $request->setUserResolver(fn() => $user);
            Auth::setUser($user);

            return $next($request);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'fail',
                'message' => $e->getMessage()
            ], 401);
        }
    }
}
