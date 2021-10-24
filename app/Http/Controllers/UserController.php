<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
	/**
	 * Function to handler Get Data User by api_token
	 */
	function show(Request $request)
	{
		$authorization = $request->header('Authorization');
		$apiToken = explode(' ', $authorization);

		$user = User::where('api_token', $apiToken[1])->first();

		return response()->json([
			"status" => true,
			"message" => "User found!",
			"data" => $user,
		], 200);
	}

	/**
	 * Function to handler Update Data User by api_token
	 */
	function update(Request $request)
	{
		$authorization = $request->header('Authorization');
		$apiToken = explode(' ', $authorization);

		$user = User::where('api_token', $apiToken[1])->first();

		$first_name = $request->input('first_name');
		$last_name = $request->input('last_name');
		$motto = $request->input('motto');
		$bio = $request->input('bio');

		$user->update([
			"first_name" => $first_name,
			"last_name" => $last_name,
			"motto" => $motto,
			"bio" => $bio,
		]);

		return response()->json([
			"status" => true,
			"message" => "Success update data!",
			"data" => $user,
		], 201);
	}

	/**
	 * Function to handler Logout User
	 */
	function logout(Request $request)
	{
		$authorization = $request->header('Authorization');
		$apiToken = explode(' ', $authorization);

		$user = User::where('api_token', $apiToken[1])->first();

		$user->update([
			"api_token" => "",
		]);

		return response()->json([
			"status" => true,
			"message" => "Success logout!",
			"data" => null,
		], 200);
	}
}
