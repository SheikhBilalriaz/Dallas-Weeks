<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Exception;

class LoginController extends Controller
{
    /**
     * Display the login page.
     *
     * @return \Illuminate\View\View
     */
    public function login()
    {
        /* Prepare data to be passed to the view, including the page title */
        $data = ['title' => 'Login - Networked'];

        /* Return the 'Login' view with the prepared data */
        return view('front.login', $data);
    }

    /**
     * Validate and authenticate user credentials.
     *
     * @param \Illuminate\Http\Request $request The incoming request containing user credentials.
     * @return \Illuminate\Http\JsonResponse Returns a JSON response with the result of the authentication attempt.
     */
    public function checkCredentials(Request $request)
    {
        try {
            /* Validate the incoming request data */
            $validator = Validator::make($request->all(), [
                'email' => 'required|email',
                'password' => 'required',
            ]);

            /* Check if validation fails and return a JSON response with the first error message */
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'error' => $validator->errors()->first()
                ], 400);
            }

            /* Attempt to authenticate the user with the provided email and password */
            if (Auth::attempt($request->only('email', 'password'))) {
                /* If authentication is successful, return a success response */
                return response()->json([
                    'success' => true,
                    'message' => 'User Authenticated Successfully.'
                ], 200);
            } else {
                /* If authentication fails, return an error response */
                return response()->json([
                    'success' => false,
                    'error' => 'Invalid Username or Password.'
                ], 400);
            }
        } catch (Exception $e) {
            /* Handle unexpected exceptions and return JSON response */
            return response()->json([
                'success' => false,
                'error' => 'An unexcepted error occured'
            ], 500);
        }
    }
    /**
     * Log out the currently authenticated user and redirect to the homepage.
     *
     * @return \Illuminate\Http\RedirectResponse  A redirect response to the homepage.
     */
    public function logoutUser()
    {
        /* If the user is not authenticated, flush (clear) the entire session. */
        session()->flush();
        
        /* Log out the currently authenticated user */
        Auth::logout();

        /* Redirect the user to the homepage after logging out */
        return redirect()->route('homePage');
    }
}
