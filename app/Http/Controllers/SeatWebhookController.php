<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Team;
use App\Models\Seat;
use App\Models\Webhook;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class SeatWebhookController extends Controller
{
    public function webhook($slug, $seat_slug)
    {
        try {
            /* Retrieve the team and seat by their slugs */
            $team = Team::where('slug', $slug)->first();
            $seat = Seat::where('slug', $seat_slug)->first();
            $seat_webhooks = Webhook::where('seat_id', $seat->id)->get();
            $seat_webhook_map = $seat_webhooks->keyBy('webhook_id');
            $uc = new UnipileController();
            $final_webhooks = [];
            do {
                $webhooks = $uc->list_all_webhook(new \Illuminate\Http\Request([]))->getData(true)['webhook'];
                $items = $webhooks['items'];
                foreach ($items as $webhook) {
                    if (isset($seat_webhook_map[$webhook['id']])) {
                        $seat_webhook_map[$webhook['id']]->webhook = $webhook;
                    }
                }
            } while (isset($webhooks['cursor']) && $webhooks['cursor'] !== null);
            $final_webhooks = $seat_webhook_map->values();

            /* Prepare data to pass to the view */
            $data = [
                'title' => 'Dashboard - Webhook',
                'team' => $team,
                'seat' => $seat,
                'webhooks' => $final_webhooks,
            ];

            /* Return the view with the seat data */
            return view('back.webhook', $data);
        } catch (Exception $e) {
            /* Log the exception message for debugging */
            Log::error($e);

            /* Redirect to the dashboard with a generic error message if an exception occurs */
            return redirect()->route('dashboardPage', ['slug' => $slug])
                ->withErrors(['error' => 'Something went wrong']);
        }
    }

    public function deleteWebhook($slug, $seat_slug, $id)
    {
        try {
            $seat = Seat::where('slug', $seat_slug)->first();

            /* Begin a database transaction */
            DB::beginTransaction();

            $uc = new UnipileController();

            $webhook = Webhook::where('id', $id)->where('seat_id', $seat->id)->firstOrFail();

            $delete_webhook = $uc->delete_webhook(new \Illuminate\Http\Request(['webhook_id' => $webhook->webhook_id]))->getData(true);

            if (isset($delete_webhook['error'])) {
                DB::rollBack();
                return response()->json(['success' => false, 'message' => 'Something went wrong'], 500);
            }

            $webhook->delete();

            /* Commit the transaction */
            DB::commit();

            /* Return a success response */
            return response()->json(['success' => true, 'message' => 'Blacklist item deleted successfully.']);
        } catch (ModelNotFoundException $e) {
            /* Return a 404 Not Found response if the item does not exist */
            return response()->json(['error' => 'Webhook item not found.'], 404);
        } catch (Exception $e) {
            /* Rollback the transaction if something went wrong */
            DB::rollBack();

            /* Log the exception message for debugging purposes */
            Log::error($e);

            /* Return a generic error response */
            return response()->json(['success' => false, 'message' => 'Something went wrong'], 500);
        }
    }
}
