<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    /**
     * Function to handler Register User
     */
    function register(Request $request)
    {
        $email = $request->input("email");
        $first_name = $request->input("first_name");
        $last_name = $request->input("last_name");
        $motto = $request->input("motto");
        $bio = $request->input("bio");
        $password = $request->input("password");

        if (!$email) {
            return response()->json([
                "status" => false,
                "message" => "Field email is required!",
                "data" => null,
            ], 400);
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return response()->json([
                "status" => false,
                "message" => "Invalid email address!",
                "data" => null,
            ], 400);
        }

        if (!$first_name) {
            return response()->json([
                "status" => false,
                "message" => "Field first_name is required!",
                "data" => null,
            ], 400);
        }

        if (!$password) {
            return response()->json([
                "status" => false,
                "message" => "Field password is required!",
                "data" => null,
            ], 400);
        }
        
        if (!validatePassword($password)) {
            return response()->json([
                "status" => false,
                "message" => "Password should be at least 8 characters and include at least one uppercase, one lowercase & one number",
                "data" => null,
            ], 400);
        }
        
        $user = User::find($email);
        
        if ($user) {
            return response()->json([
                "status" => false,
                "message" => "User with email " . $email . " already exists!",
                "data" => null,
            ], 400);
        }

        $new_user = User::create([
            "email" => $email,
            "first_name" => $first_name,
            "last_name" => $last_name,
            "motto" => $motto,
            "bio" => $bio,
            "password" => Hash::make($password),
            "api_token" => '',
        ]);

        return response()->json([
            "status" => true,
            "message" => "Registration Success!",
            "data" => $new_user,
        ], 201);
    }
    
    /**
     * Function to handler Login User
     */
    function login(Request $request)
    {
        $email = $request->input("email");
        $password = $request->input("password");

        if (!$email) {
            return response()->json([
                "status" => false,
                "message" => "Field email is required!",
                "data" => null,
            ], 400);
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return response()->json([
                "status" => false,
                "message" => "Invalid email address!",
                "data" => null,
            ], 400);
        }

        if (!$password) {
            return response()->json([
                "status" => false,
                "message" => "Field password is required!",
                "data" => null,
            ], 400);
        }

        $user = User::where('email', $email)->first();

        if (!$user) {
            return response()->json([
                "status" => false,
                "message" => "User with email " . $email . " not found!",
                "data" => null,
            ], 400);
        }
        
        if (!Hash::check($password, $user->password)) {
            return response()->json([
                "status" => false,
                "message" => "Wrong password!",
                "data" => null,
            ], 400);
        }

        $apiToken = base64_encode(Str::random(64));
        $user->update([
            "api_token" => $apiToken,
        ]);

        return response()->json([
            "status" => true,
            "message" => "Login Success!",
            "data" => [
                "user" => $user,
                "token" => $apiToken,
            ],
        ], 200);
    }
}

function validatePassword($password)
{
    // Validate password strength
    $uppercase = preg_match('@[A-Z]@', $password);
    $lowercase = preg_match('@[a-z]@', $password);
    $number    = preg_match('@[0-9]@', $password);

    if ($uppercase && $lowercase && $number && strlen($password) > 8) {
        return true;
    }

    return false;
}
