<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserRequest;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Register a new user and issue a JWT token.
     */
    public function register(UserRequest $request)
    {

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'role' => $request->role,
        ]);

        $token = JWTAuth::fromUser($user);
        return response()->json(compact('user', 'token'), 201);
    }

    /**
     * Login user and issue a JWT token.
     */
    public function login(Request $request)
    {

        $credentials = $request->only('email', 'password');
        if ($token = JWTAuth::attempt($credentials)) {
            return response()->json(['token' => $token]);
        }

        return response()->json(['error' => 'Unauthorized'], 401);
    }

    public function logout()
    {
        JWTAuth::invalidate(JWTAuth::getToken());

        return response()->json(['message' => 'Successfully logged out']);
    }
}
