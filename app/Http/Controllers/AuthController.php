<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Validator;
use JWTAuth;
use Hash;

class AuthController extends Controller
{
    /**
     * Login the user and return an authentication token
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function login(Request $request)
    {
        // Validate the request
        $request->validate([
            'data.email' => 'required',
            'data.password' => 'required'
        ]);

        // Find the user with an email that matches the provided
        $user = User::where('email', $request->input('data.email'))->firstOrFail();

        // Check if the password matches the email
        if (!Hash::check($request->input('data.password'), $user->password)) {
            throw ValidationException::withMessages(['Invalid credentials']);
        }

        // Set the expiration date of the token
        $expire = strtotime('+7 day');

        // Attach custom claims to the token
        $customClaims = [
            'name' => $user->name,
            'exp' => $expire,
            'roles' => $user->getRoleNames()
        ];

        // Create the token
        $access_token = JWTAuth::fromUser($user, $customClaims);

        // Set up the response format
        $response = array(
            'data' => array(
                'access_token' => $access_token,
                'expire' => date('Y-m-d H:i:s', $expire)
            )
        );

        return response()->json($response);
    }

    /**
     * Logout the user and return an OK response if successful
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function logout(Request $request)
    {
        // Invalidate the provided token
        JWTAuth::parseToken()->invalidate();

        return response()->json();
    }
}
