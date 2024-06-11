<?php

namespace App\Http\Controllers;

use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string',
                'email' => 'required|string|email|unique:users',
                'password' => 'required|string|min:8',
            ]);

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);

            if ($user) {
                $token = $user->createToken('auth_token')->plainTextToken;
                return response()->json([
                    'status' => "success",
                    'access_token' => $token,
                    'token_type' => 'Bearer',
                ], 201);
            }
        } catch (ValidationException $e) {
            return response()->json([
                'status' => "error",
                'message' => $e->getMessage()
            ], 400);
        } catch (Exception $e) {
            return response()->json([
                'status' => "error",
                'message' => 'Something went wrong'
            ], 500);
        }
    }

    public function login(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|string|email',
                'password' => 'required|string',
            ]);
            $user = User::where('email', $request->email)->first();

            if (!$user) {
                return response()->json([
                    'status' => "error",
                    'message' => 'User not found'
                ], 404);
            } else if (!Hash::check($request->password, $user->password)) {
                return response()->json([
                    'status' => "error",
                    'message' => 'Incorrect password'
                ], 401);
            }

            $token = $user->createToken('auth_token')->plainTextToken;
            return response()->json([
                'status' => "success",
                'access_token' => $token,
                'token_type' => 'Bearer',
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => "error",
                'message' => 'Something went wrong'
            ], 500);
        }
    }

    public function logout(Request $request)
    {
        try {
            $request->user()->tokens()->delete();
            return response()->json([
                'status' => "success",
                'message' => 'Logout successful'
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => "error",
                'message' => 'Something went wrong'
            ], 500);
        }
    }
}
