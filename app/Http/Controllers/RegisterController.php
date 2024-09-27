<?php

namespace App\Http\Controllers;

use App\Mail\WelcomeMail;
use App\Models\Global_Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use App\Models\Team;
use App\Models\Team_Member;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Exception;
use Illuminate\Support\Facades\Auth;

class RegisterController extends Controller
{
    /**
     * Display the registration page.
     *
     * @return \Illuminate\View\View
     */
    public function register()
    {
        /* Set the title for the registration page */
        $data = ['title' => 'Register - Networked'];

        /* Render the 'signup' view with the provided data */
        return view('front.signup', $data);
    }

    /**
     * Register a new user and create associated records like team, assigned seat, and permissions.
     * 
     * @param Request $request The incoming HTTP request containing user registration details.
     * @return RedirectResponse Redirects to the login page on success or back to the registration form on failure.
     */
    public function registerUser(Request $request)
    {
        /* Validate request data */
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email|unique:user',
            'password' => [
                'required',
                'min:8',
                'regex:/^(?=.*[!@#$%^&*(),.?":{}|<>]).*$/',
                'confirmed'
            ],
            'company' => 'required',
            'termsCheckbox' => 'required'
        ], [
            'password.regex' => 'The password must include at least one special character.',
            'termsCheckbox.required' => 'Terms and conditions must be checked'
        ]);

        /* Return validation errors if validation fails */
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        /* Begin a database transaction */
        DB::beginTransaction();

        try {
            /* Create a new user with the provided name, email, and hashed password */
            $user = User::create([
                'name' => $request->input('name'),
                'email' => $request->input('email'),
                'password' => Hash::make($request->input('password')),
                'remember_token' => Str::random(100),
            ]);

            /* Create a new team with the provided company name */
            $team = Team::create([
                'name' => $request->input('company'),
                'slug' => $this->createUniqueSlug($request->input('company')),
                'creator_id' => $user->id,
            ]);

            /* Create a new team member entry */
            $team_member = Team_Member::create([
                'user_id' => $user->id,
                'team_id' => $team->id,
            ]);

            /* Define permissions to be created for the user */
            $permissions = [
                ['permission_name' => 'Manage payment system', 'permission_slug' => 'manage_payment_system'],
                ['permission_name' => 'Manage global blacklist', 'permission_slug' => 'manage_global_blacklist']
            ];

            $deleteable_permissions = [];

            /* Bulk insert the permissions for better efficiency */
            foreach ($permissions as $permission) {
                $deleteable_permissions[] = Global_Permission::create([
                    'name' => $permission['permission_name'],
                    'slug' => $permission['permission_slug'],
                    'user_id' => $user->id,
                    'team_id' => $team->id,
                    'access' => 1
                ]);
            }

            /* Commit the transaction as all operations succeeded */
            DB::commit();

            /* Send a welcome email to the newly registered user */
            Mail::to($user->email)->send(new WelcomeMail($user));

            /* Redirect to the login page with a success message */
            return redirect()->route('loginPage')->with('success', 'User registered successfully');
        } catch (Exception $e) {
            /* Rollback the transaction if something fails */
            DB::rollBack();

            /* Delete created entities only if they exist */
            if (!empty($deleteable_permission)) {
                foreach ($deleteable_permissions as $deleteable_permission) {
                    if (!empty($deleteable_permission) && !empty($deleteable_permission->id)) {
                        $deleteable_permission->delete();
                    }
                }
            }
            if (!empty($team_member) && !empty($team_member->id)) {
                $team_member->delete();
            }
            if (!empty($team) && !empty($team->id)) {
                $team->delete();
            }
            if (!empty($user) && !empty($user->id)) {
                $user->delete();
            }

            /* Log the exception message for debugging */
            Log::error($e);

            /* Redirect back with an error message */
            return redirect()->back()->withErrors(['error' => 'Something went wrong'])->withInput();
        }
    }

    /**
     * Return an unique slug for team
     * 
     * @param String $teamName
     * @return String $slug
     */
    private function createUniqueSlug($teamName)
    {
        /* Generate the initial slug from the team name */
        $slug = Str::slug($teamName);
        $originalSlug = $slug;

        /* Keep appending random numbers until the slug is unique */
        while (Team::where('slug', $slug)->exists()) {
            /* Generate a random 5-digit number */
            $randomNumber = rand(10000, 99999);

            /* Append the random number to the slug */
            $slug = $originalSlug . '-' . $randomNumber;
        }

        /* Return the unique slug */
        return $slug;
    }

    /**
     * Verify the email address of a user.
     * 
     * @param  string  $email  The email address to verify.
     * @return \Illuminate\Http\RedirectResponse  Redirects to the login page with either a success or error message.
     */
    public function verifyAnEmail($email, $token)
    {
        try {
            /* Attempt to find the user by their email address */
            $user = User::where('email', $email)->first();

            /* Check if the user was found */
            if (empty($user)) {
                return redirect()->route('loginPage')->withErrors(['error' => 'User not found']);
            }

            /* Check if the email has already been verified */
            if (!empty($user->verified_at)) {
                /* If the email is already verified, redirect to the login page with a success message. */
                return redirect()->route('loginPage')->with([
                    'success' => 'Email already verified',
                    'email' => $user->email
                ]);
            }

            if ($user->remember_token != $token) {
                return redirect()->route('loginPage')->withErrors(['error' => 'Invalid token']);
            }

            /* If the email is not verified, set the `verified_at` field to the current timestamp. */
            $user->verified_at = now();
            $user->updated_at = now();
            $user->save();

            /* Redirect to the login page with a success message indicating that the email verification was successful. */
            return redirect()->route('loginPage')->with([
                'success' => 'Email Verification Successful',
                'email' => $user->email
            ]);
        } catch (\Exception $e) {
            /* If an exception occurs, log the exception message for debugging purposes. */
            Log::error($e);

            /* Redirect to the login page with an error message indicating. */
            return redirect()->route('loginPage')->withErrors(['error' => 'Something went wrong']);
        }
    }

    /**
     * Resend a welcome email to the currently authenticated user.
     * 
     * @return RedirectResponse Redirects back with a success message if the email is sent successfully, 
     * or with an error message if something goes wrong.
     */
    public function resendAnEmail()
    {
        try {
            /* Retrieve the currently authenticated user */
            $user = Auth::user();
            $user = User::find($user->id);

            /* Wrap the token update and email sending in a transaction */
            DB::transaction(function () use ($user) {
                /* Regenerate the remember token */
                $user->update([
                    'remember_token' => Str::random(100),
                    'updated_at' => now()
                ]);

                /* Send a welcome email */
                Mail::to($user->email)->send(new WelcomeMail($user));
            });

            /* Return with success message */
            return redirect()->back()->with('success', 'Email sent successfully.');
        } catch (\Throwable $e) {
            /* Log the exception message for debugging purposes */
            Log::error($e);

            /* Redirect to login with an error message */
            return redirect()->route('loginPage')->withErrors(['error' => 'An unexpected error occurred. Please try again.']);
        }
    }
}
