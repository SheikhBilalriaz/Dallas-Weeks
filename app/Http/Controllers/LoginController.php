<?php

namespace App\Http\Controllers;

use App\Mail\ForgotPasswordMail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class LoginController extends Controller
{
    /**
     * Display the login page.
     *
     * @return \Illuminate\View\View
     */
    public function login(Request $request)
    {
        /* Retrieve 'isPassword' from the query parameters, defaulting to false if not provided */
        $isPassword = $request->query('isPassword', false);
        $email = $request->query('email', null);

        /* Prepare data to be passed to the view, including the page title */
        $data = ['title' => 'Login - Networked'];

        /* Return the 'Login' view with the prepared data */
        return view('front.login', $data)->with(['is_password' => $isPassword, 'forget_email' => $email]);
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
                ], 401);
            }
        } catch (Exception $e) {
            /* If an exception occurs, log the exception message for debugging purposes. */
            Log::error($e);

            /* Handle unexpected exceptions and return JSON response */
            return response()->json([
                'success' => false,
                'error' => 'Something went wrong'
            ], 500);
        }
    }

    public function forgotPassword(Request $request)
    {
        try {
            /* Validate the incoming request data */
            $validator = Validator::make($request->all(), [
                'email' => 'required|email',
            ]);

            /* Check if validation fails and return a JSON response with the first error message */
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'error' => $validator->errors()->first()
                ]);
            }

            /* Check if user exists or not */
            if (!User::where('email', $request->input('email'))->exists()) {
                return response()->json([
                    'success' => false,
                    'error' => 'User not found'
                ]);
            }

            /* Retrieve the email from input */
            $email = $request->input('email');

            /* Send a welcome email to the newly registered user */
            Mail::to($email)->send(new ForgotPasswordMail($email));

            /* Handle unexpected exceptions and return JSON response */
            return response()->json([
                'success' => true,
                'message' => 'Email sent successfully'
            ]);
        } catch (Exception $e) {
            /* If an exception occurs, log the exception message for debugging purposes. */
            Log::error($e);

            /* Handle unexpected exceptions and return JSON response */
            return response()->json([
                'success' => false,
                'error' => 'Something went wrong'
            ], 500);
        }
    }

    public function updatePassword(Request $request)
    {
        try {
            /* Validate the incoming request data */
            $validator = Validator::make($request->all(), [
                'password' => 'required|confirm',
            ]);

            /* Check if validation fails and return a JSON response with the first error message */
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'error' => $validator->errors()->first()
                ]);
            }
        } catch (Exception $e) {
            /* If an exception occurs, log the exception message for debugging purposes. */
            Log::error($e);

            /* Handle unexpected exceptions and return JSON response */
            return response()->json([
                'success' => false,
                'error' => 'Something went wrong'
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
        /* Log out the currently authenticated user */
        Auth::logout();

        /* Invalidate the session and regenerate CSRF token */
        session()->invalidate();
        session()->regenerateToken();

        /* Redirect the user to the homepage after logging out */
        return redirect()->route('homePage');
    }
}
