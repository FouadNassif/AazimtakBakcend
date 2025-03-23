<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Guest;
use App\Models\Wedding;
use App\Models\WeddingDetail;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\PersonalAccessToken;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Validator;


class UserController extends Controller
{

    public function signup(Request $request)
    {
        $request->validate([
            'username' => 'required|string|max:255|unique:users',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'phonenumber' => 'required|',
        ]);

        try {
            $user = User::create([
                'username' => $request->username,
                'password' => Hash::make($request->password),
                'email' => $request->email,
                'phonenumber' => $request->phonenumber,
                'role_id' => 3,
                'subscription_id' => 1,
            ]);

            $token = $user->createToken('auth-token')->plainTextToken;

            return response()->json([
                'user' => $user,
                'token' => $token
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to register user: ' . $e->getMessage()], 500);
        }
    }


    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username_or_email' => 'required|string',
            'password' => 'required|string',
        ]);

        $user = filter_var($request->username_or_email, FILTER_VALIDATE_EMAIL)
            ? User::where('email', $request->username_or_email)->first()
            : User::where('username', $request->username_or_email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'error' => ['Invalid credentials.'],
            ])->status(401);
        }

        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token,
        ], 200);
    }

    public function checkAuthByToken(Request $request)
    {
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json([
                'message' => 'Token not provided',
            ], 400);
        }

        $userToken = PersonalAccessToken::findToken($token);

        if (!$userToken) {
            return response()->json([
                'message' => 'Invalid or expired token',
            ], 401);
        }

        $user = $userToken->tokenable;

        $expiresAt = $userToken->created_at->addDays(7);

        if (Carbon::now()->greaterThan($expiresAt)) {
            return response()->json([
                'message' => 'Token has expired',
            ], 401);
        }

        return response()->json([
            'authenticated' => true,
            'user' => $user,
        ]);
    }
}
