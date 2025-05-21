<?php

namespace App\Http\Controllers;

use App\Models\User;
use Hidehalo\Nanoid\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    private $nanoid;

    public function __construct()
    {
        $this->nanoid = new Client();
    }

    public function store(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'username' => 'required|string',
                'password' => 'required|string',
                'fullname' => 'required|string',
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'status' => 'fail',
                'message' => 'Invalid user data'
            ], 400);
        }

        $validated = $validator->validate();

        if (User::where('username', $validated['username'])->first()) {
            return response()->json([
                'status' => 'fail',
                'message' => 'Username already used'
            ], 400);
        }

        $user = User::create([
            'id' => 'user_' . $this->nanoid->generateId(32),
            'username' => $validated['username'],
            'password' => Hash::make($validated['password']),
            'fullname' => $validated['fullname'],
        ]);

        return response()->json([
            'status' => 'success',
            'data' => [
                'userId' => $user->id
            ]
        ], 201);
    }
}
