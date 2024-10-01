<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Seat;
use App\Models\Company_Info;
use App\Models\Seat_Info;
use Illuminate\Http\Request;
use App\Models\Team;
use App\Models\Team_Member;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class InvoiceController extends Controller
{
    /**
     * Display the invoice page for the authenticated user.
     *
     * @param string $slug
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function invoice($slug)
    {
        try {
            /* Find the team by slug */
            $team = Team::where('slug', $slug)->first();

            /* Find seats related to team */
            $seats = Seat::where('team_id', $team->id)->get();

            /* Extract the invoices based on seat IDs */
            $invoices = Invoice::whereIn('seat_id', $seats->pluck('id')->toArray())->get();

            /* Prepare data for the view */
            $data = [
                'title' => 'Invoices - Networked',
                'team' => $team,
                'seats' => $seats,
                'invoices' => $invoices,
            ];

            /* Return the view with the data */
            return view('dashboard.invoice', $data);
        } catch (Exception $e) {
            /* Log the exception message for debugging purposes. */
            Log::error($e);

            /* Redirect to dashboardPage with an error message if an exception occurs. */
            return redirect()->route('dashboardPage')->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Download the invoice PDF for the given invoice ID and team slug.
     *
     * @param string $slug The team slug.
     * @param int $id The invoice ID.
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse|\Illuminate\Http\JsonResponse
     */
    public function downloadInvoice($slug, $id)
    {
        /* Find the invoice by its ID */
        $invoice = Invoice::find($id);

        /* Check if the invoice exists */
        if (!$invoice) {
            /* Redirect to globalInvoicePage with an error message if an exception occurs. */
            return redirect()->route('globalInvoicePage')->withErrors(['error' => 'Invoice not found']);
        }

        /* Build the file path using the invoice URL stored in the database */
        $filePath = 'invoices/' . $invoice->invoice_url;

        /* Check if the file exists in the 'public' disk storage */
        if (!Storage::disk('public')->exists($filePath)) {
            /* Redirect to globalInvoicePage with an error message if an exception occurs. */
            return redirect()->route('globalInvoicePage')->withErrors(['error' => 'File not found']);
        }

        /* Return the file for download */
        return Storage::disk('public')->download($filePath);
    }

    /**
     * Retrieve invoices based on the team slug and seat ID.
     *
     * @param string $slug The team slug.
     * @param mixed $id The seat ID ('all' for all seats).
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function invoiceBySeat($slug, $id)
    {
        try {
            /* Find the team by slug */
            $team = Team::where('slug', $slug)->first();

            /* Build the seat query based on whether all seats or a specific seat is requested */
            $query = Seat::where('team_id', $team->id);
            if ($id != 'all') {
                $query->where('id', $id);
            }

            /* Get the related seats */
            $seats = $query->get();

            /* If invoices exist, populate additional data and return */
            $invoices = Invoice::whereIn('seat_id', $seats->pluck('id')->toArray())->get();
            if ($invoices->isNotEmpty()) {
                \Stripe\Stripe::setApiKey(config('services.stripe.secret'));
                foreach ($invoices as $invoice) {
                    $invoice->seat = Seat::find($invoice->seat_id);
                    $invoice->company_info = Company_Info::find($invoice->seat->company_info_id);
                    $invoice->seat_info = Seat_Info::find($invoice->seat->seat_info_id);
                    $invoice->stripe_invoice = \Stripe\Invoice::retrieve($invoice->invoice_id);
                }
                return response()->json(['success' => true, 'invoices' => $invoices]);
            }

            /* If no invoices are found, return a message */
            return response()->json(['success' => false, 'message' => 'No invoices found.'], 404);
        } catch (Exception $e) {
            /* Log the exception message for debugging purposes. */
            Log::error($e);

            /* Redirect to globalInvoicePage with an error message if an exception occurs. */
            return redirect()->route('globalInvoicePage')->withErrors(['error' => $e->getMessage()]);
        }
    }
}
