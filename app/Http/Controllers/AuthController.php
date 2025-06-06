<?php

namespace App\Http\Controllers;

use App\Models\Authentication;
use App\Models\User;
use App\Services\JwtService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    private $jwtService;

    public function __construct()
    {
        $this->jwtService = new JwtService();
    }

    public function store(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'username' => 'required|string',
                'password' => 'required|string',
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'status' => 'fail',
                'message' => 'Invalid parameters'
            ], 400);
        }

        $validatedData = $validator->validate();

        $user = User::where('username', $validatedData['username'])->first();

        if (!$user || !Hash::check($validatedData['password'], $user->password)) {
            return response()->json([
                'status' => 'fail',
                'message' => 'Invalid credentials'
            ], 401);
        }

        $accessToken = $this->jwtService->generateAccessToken($user);
        $refreshToken = $this->jwtService->generateRefreshToken($user);

        Authentication::create([
            'access_token' => $accessToken,
            'refresh_token' => $refreshToken,
        ]);

        return response()->json([
            'status' => 'success',
            'data' => [
                'accessToken' => $accessToken,
                'refreshToken' => $refreshToken,
            ]
        ], 201);
    }

    public function update(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'refreshToken' => 'required|string'
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'status' => 'fail',
                'message' => 'Invalid parameters'
            ], 400);
        }

        $validatedData = $validator->validate();

        $authentication = Authentication::where('refresh_token', $validatedData['refreshToken'])->first();

        if ($authentication) {
            $refreshToken = $authentication->refresh_token;
            $refreshTokenClaims = $this->jwtService->parseToken($refreshToken)->claims();

            $user = User::find($refreshTokenClaims->get('userId'));

            if ($user) {
                $accessToken = $this->jwtService->generateAccessToken($user);

                Authentication::create([
                    'access_token' => $accessToken,
                    'refresh_token' => $refreshToken,
                ]);

                return response()->json([
                    'status' => 'success',
                    'data' => [
                        'accessToken' => $accessToken,
                    ]
                ]);
            }
        }

        return response()->json([
            'status' => 'fail',
            'message' => 'Invalid authentication data'
        ], 400);
    }

    public function destroy(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'refreshToken' => 'required|string'
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'status' => 'fail',
                'message' => 'Invalid parameters'
            ], 400);
        }

        $validatedData = $validator->validate();

        $authentications = Authentication::where('refresh_token', $validatedData['refreshToken']);

        if ($authentications->count() <= 0) {
            return response()->json([
                'status' => 'fail',
                'message' => 'Authentication not found'
            ], 400);
        }

        $authentications->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Authentication deleted successfully'
        ]);
    }
}
