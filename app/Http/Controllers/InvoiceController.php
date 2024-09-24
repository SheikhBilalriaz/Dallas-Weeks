<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Team;
use App\Models\Team_Member;
use Exception;
use Illuminate\Support\Facades\Log;

class InvoiceController extends Controller
{
    /**
     * Display the invoice page for the authenticated user.
     *
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function invoice($slug)
    {
        try {
            /* Retrieve the team associated with the slug */
            $team = Team::where('slug', $slug)->first();

            /* Check if the user has permission to manage the payment system. */
            if (!session()->has('is_manage_payment_system') || !session('is_manage_payment_system')) {
                /* Redirect if the user does not have permission to manage the payment systemt. */
                return redirect()->route('dashboardPage', ['slug' => $team->slug])->withErrors(['error' => "You don't have access to payment and invoices"]);
            }

            $members = Team_Member::where('team_id', $team->id)->get();

            /* Prepare data for the view. */
            $data = [
                'title' => 'Invoices - Networked',
                'team' => $team,
                'members' => $members,
            ];

            /* Return the view with the prepared data. */
            return view('dashboard.invoice', $data);
        } catch (Exception $e) {
            /* Log the exception message for debugging purposes. */
            Log::error($e);

            /* Redirect to login with an error message if an exception occurs. */
            return redirect()->route('loginPage')->withErrors(['error' => $e->getMessage()]);
        }
    }
}
