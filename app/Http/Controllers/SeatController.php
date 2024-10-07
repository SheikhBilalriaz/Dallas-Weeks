<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\Assigned_Seat;
use App\Models\Company_Info;
use App\Models\Permission;
use App\Models\Role_Permission;
use App\Models\Seat;
use App\Models\Seat_Info;
use App\Models\Team;
use App\Models\Team_Member;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class SeatController extends Controller
{
    /**
     * Filter seats based on the search term and retrieve additional account information.
     *
     * @param string $slug
     * @param string $search The search term to filter seat names.
     * @return \Illuminate\Http\JsonResponse The JSON response with the filtered seats and their statuses.
     */
    public function filterSeat($slug, $search)
    {
        try {
            /* Get the currently authenticated user */
            $user = Auth::user();

            /* Retrieve the team associated with the slug */
            $team = Team::where('slug', $slug)->first();

            /* Initialize an empty array for filtered seats */
            $seats = [];

            /* Check if the user is the team creator or a member */
            if ($user->id == $team->creator_id) {
                /* If the user is the team creator, retrieve all seats for the team */
                $seats = Seat::where('team_id', $team->id)->get();
            } else {
                /* Retrieve the member and assigned seats for the user */
                $assignedSeatIds = Assigned_Seat::whereIn('member_id', Team_Member::where('user_id', $user->id)
                    ->where('team_id', $team->id)
                    ->pluck('id'))
                    ->pluck('seat_id');
                $seats = Seat::whereIn('id', $assignedSeatIds)->get();
            }

            if ($search !== 'null') {
                /* Filter seats based on company information name matching the search term */
                $filteredSeats = $seats->filter(function ($seat) use ($search) {
                    return Company_Info::where('id', $seat->company_info_id)
                        ->where('name', 'LIKE', '%' . $search . '%')
                        ->exists();
                });
            } else {
                $filteredSeats = $seats;
            }

            /* Check if any seats were found and return the response */
            if ($filteredSeats->isNotEmpty()) {
                foreach ($filteredSeats as $seat) {
                    $seat->company_info = Company_Info::where('id', $seat->company_info_id)->first();
                }
                return response()->json(['success' => true, 'seats' => $filteredSeats]);
            }

            /* Seat not found */
            return response()->json(['success' => false, 'message' => 'No seat items found.'], 404);
        } catch (Exception $e) {
            /* Log the exception message for debugging purposes */
            Log::error($e);

            /* Return a generic error response */
            return response()->json(['success' => false, 'message' => 'Something went wrong'], 500);
        }
    }

    /**
     * Retrieve seat details by seat ID for the authenticated user.
     *
     * @param string $slug
     * @param int $seat_id The ID of the seat to retrieve.
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSeatById($slug, $seat_id)
    {
        try {
            /* Get the currently authenticated user */
            $user = Auth::user();

            /* Retrieve the team associated with the slug */
            $team = Team::where('slug', $slug)->first();

            /* Find the seat for the user based on the provided seat ID */
            $seat = Seat::where('team_id', $team->id)->where('id', $seat_id)->firstOrFail();

            /* Retrieve the member and assigned seats for the user */
            $member = Team_Member::where('user_id', $user->id)->where('team_id', $team->id)->first();

            /* Access control: If not creator and no valid assigned seat, deny access */
            if (
                $user->id != $team->creator_id &&
                (!empty($member) && !Assigned_Seat::where('member_id', $member->id)->where('seat_id', $seat->id)->exists())
            ) {
                return response()->json(['success' => false, 'errors' => 'You do not have access to this seat'], 403);
            }

            $seat->seat_info = Seat_Info::where('id', $seat->seat_info_id)->first();
            $seat->company_info = Company_Info::where('id', $seat->company_info_id)->first();

            $data = [
                'success' => true,
                'seat' => $seat
            ];

            if ($user->id == $team->creator_id) {
                $data['manage_seat_settings'] = true;
                $data['cancel_subscription'] = true;
                $data['delete_seat'] = true;
            } else {
                $assignedSeat = Assigned_Seat::where('member_id', $member->id)->where('seat_id', $seat->id)->first();
                $role = Role::find($assignedSeat->id);
                $permissions = Permission::whereIn('slug', ['manage_seat_settings', 'cancel_subscription', 'delete_seat'])->get();
                $data['manage_seat_settings'] = Role_Permission::where('role_id', $role->id)
                    ->where('permission_id', $permissions->where('slug', 'manage_seat_settings')->pluck('id'))
                    ->value('access') ?? false;
                $data['cancel_subscription'] = Role_Permission::where('role_id', $role->id)
                    ->where('permission_id', $permissions->where('slug', 'cancel_subscription')->pluck('id'))
                    ->value('access') ?? false;
                $data['delete_seat'] = Role_Permission::where('role_id', $role->id)
                    ->where('permission_id', $permissions->where('slug', 'delete_seat')->pluck('id'))
                    ->value('access') ?? false;
            }

            return response()->json($data);
        } catch (ModelNotFoundException $e) {
            /* Return a 404 response if any model (SeatInfo, AssignedSeats, Role) is not found */
            return response()->json(['success' => false, 'errors' => 'Seat Not Found'], 404);
        } catch (Exception $e) {
            /* Log the exception for debugging purposes */
            Log::info($e);

            /* Return a JSON response with the error message and a 404 status code */
            return response()->json(['success' => false, 'errors' => 'Something went wrong'], 500);
        }
    }
}
