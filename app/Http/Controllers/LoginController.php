<?php

namespace App\Http\Controllers;

use App\Mail\ForgotPasswordMail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Exception;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class LoginController extends Controller
{
    /**
     * Display the login page.
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function login(Request $request)
    {
        /* Retrieve 'isPassword' from query parameters, defaulting to false if not provided */
        $isPassword = $request->query('isPassword', false);

        /* Retrieve 'email' from query parameters, null if not provided */
        $email = $request->query('email', null);

        /* Prepare data array to pass to the view with the page title */
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

            /* If validation fails, return a JSON response with the first error message */
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'error' => $validator->errors()->first()
                ], 400);
            }

            /* Attempt to authenticate the user with provided email and password */
            if (Auth::attempt($request->only('email', 'password'))) {
                /* Authentication successful, return a success response */
                return response()->json([
                    'success' => true,
                    'message' => 'User Authenticated Successfully.'
                ], 200);
            }
            
            /* Authentication failed, return an error response for invalid credentials */
            return response()->json([
                'success' => false,
                'error' => 'Invalid Username or Password.'
            ], 401);
        } catch (Exception $e) {
            /* Log the exception message for debugging purposes */
            Log::error($e);

            /* Handle unexpected exceptions and return a generic error response */
            return response()->json([
                'success' => false,
                'error' => 'Something went wrong'
            ], 500);
        }
    }

    /**
     * Handle the forgot password request by validating the user's email 
     * and sending a password reset email.
     *
     * @param \Illuminate\Http\Request $request The incoming request containing the user's email.
     * @return \Illuminate\Http\JsonResponse Returns a JSON response indicating the result of the operation.
     */
    public function forgotPassword(Request $request)
    {
        try {
            /* Validate the incoming request data */
            $validator = Validator::make($request->all(), [
                'email' => 'required|email',
            ]);

            /* If validation fails, return a JSON response with the first validation error */
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'error' => $validator->errors()->first()
                ]);
            }

            /* Check if a user with the provided email exists in the database */
            $email = $request->input('email');
            if (!User::where('email', $email)->exists()) {
                return response()->json([
                    'success' => false,
                    'error' => 'User not found'
                ]);
            }

            /* Send the forgot password email to the user */
            Mail::to($email)->send(new ForgotPasswordMail($email));

            /* Return a success response indicating that the email was sent */
            return response()->json([
                'success' => true,
                'message' => 'Email sent successfully'
            ]);
        } catch (Exception $e) {
            /* Log the exception message for debugging purposes */
            Log::error($e);

            /* Handle unexpected exceptions and return a generic error response */
            return response()->json([
                'success' => false,
                'error' => 'Something went wrong'
            ], 500);
        }
    }

    /**
     * Update the user's password.
     *
     * @param \Illuminate\Http\Request $request The incoming request containing the new password and email.
     * @return \Illuminate\Http\JsonResponse Returns a JSON response indicating the result of the operation.
     */
    public function updatePassword(Request $request)
    {
        try {
            /* Validate the request data for the new password */
            $validator = Validator::make($request->all(), [
                'new_password' => 'required|string|min:8|confirmed',
            ]);

            /* If validation fails, return a JSON response with the first validation error */
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'error' => $validator->errors()->first()
                ]);
            }

            /* Check if a user with the provided email exists in the database */
            $email = $request->input('forgetEmail');
            if (!User::where('email', $email)->exists()) {
                return response()->json([
                    'success' => false,
                    'error' => 'User not found'
                ]);
            }

            /* Retrieve the user instance by email */
            $user = User::where('email', $email)->first();
            $user->password = Hash::make($request->input('new_password'));
            $user->updated_at = now();
            $user->save();
            
            /* Return a success response indicating that the email was sent */
            return response()->json([
                'success' => true,
                'message' => 'Password updated successfully'
            ], 200);
        } catch (Exception $e) {
            /* Log the exception message for debugging purposes */
            Log::error($e);

            /* Handle unexpected exceptions and return a generic error response */
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

        /* Invalidate the current session to prevent further use */
        session()->invalidate();

        /* Regenerate the CSRF token to ensure session security for future requests */
        session()->regenerateToken();

        /* Redirect the user to the homepage after successful logout */
        return redirect()->route('homePage');
    }
}
