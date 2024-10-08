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
                $member = Team_Member::where('user_id', $user->id)->where('team_id', $team->id)->first();
                $assignedSeatIds = Assigned_Seat::where('member_id', $member->id)->pluck('seat_id');
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
     * Retrieve seat access information for a user based on the search term and seat ID.
     *
     * @param string $slug The slug identifying the team.
     * @param int $seat_id The ID of the seat to retrieve access for.
     * @return \Illuminate\Http\JsonResponse The JSON response with access details and seat status.
     */
    public function getSeatAccess($slug, $seat_id)
    {
        try {
            /* Get the currently authenticated user */
            $user = Auth::user();

            /* Retrieve the team associated with the slug */
            $team = Team::where('slug', $slug)->first();

            /* Find the seat for the user based on the provided seat ID */
            $seat = Seat::where('team_id', $team->id)->where('id', $seat_id)->firstOrFail();

            /* Check if the authenticated user is the creator of the team */
            if ($user->id == $team->creator_id) {
                /* If the user is the creator, they automatically have access to the seat */
                return response()->json(['success' => true, 'access' => true, 'active' => $seat->is_active]);
            }

            /* Retrieve the member and assigned seats for the user */
            $member = Team_Member::where('user_id', $user->id)->where('team_id', $team->id)->first();

            /* Check if the user is a team member and assigned to the seat */
            if ($member && Assigned_Seat::where('member_id', $member->id)->where('seat_id', $seat->id)->exists()) {
                /* If assigned, grant access */
                return response()->json(['success' => true, 'access' => true, 'active' => $seat->is_active]);
            }

            /* If the user is not the creator or doesn't have an assigned seat, deny access */
            return response()->json(['success' => false, 'errors' => 'You do not have access to this seat'], 403);
        } catch (ModelNotFoundException $e) {
            /* Return a 404 response if any model (SeatInfo, AssignedSeats, Role) is not found */
            return response()->json(['success' => false, 'errors' => 'Seat Not Found'], 404);
        } catch (Exception $e) {
            /* Log the exception for debugging purposes */
            Log::error($e);

            /* Return a JSON response with the error message and a 404 status code */
            return response()->json(['success' => false, 'errors' => 'Something went wrong'], 500);
        }
    }

    /**
     * Retrieve seat details by seat ID for the authenticated user.
     *
     * @param string $slug The unique identifier of the team.
     * @param int $seat_id The ID of the seat to retrieve.
     * @return \Illuminate\Http\JsonResponse JSON response with seat details and permissions.
     */
    public function getSeatById($slug, $seat_id)
    {
        try {
            /* Get the currently authenticated user */
            $user = Auth::user();

            /* Retrieve the team associated with the slug */
            $team = Team::where('slug', $slug)->first();

            /* Find the seat for the given team and seat ID */
            $seat = Seat::where('team_id', $team->id)->where('id', $seat_id)->firstOrFail();

            /* Retrieve the member associated with the team and the user */
            $member = Team_Member::where('user_id', $user->id)->where('team_id', $team->id)->first();

            /* Access control: Check if the user is the team creator or has an assigned seat */
            if ($user->id != $team->creator_id) {
                if (!$member || !Assigned_Seat::where('member_id', $member->id)->where('seat_id', $seat->id)->exists()) {
                    return response()->json(['success' => false, 'errors' => 'You do not have access to this seat'], 403);
                }
            }

            /* Retrieve Seat info and Company info of the seat */
            $seat->seat_info = Seat_Info::where('id', $seat->seat_info_id)->first();
            $seat->company_info = Company_Info::where('id', $seat->company_info_id)->first();

            /* Prepare data to be returned */
            $data = [
                'success' => true,
                'seat' => $seat
            ];

            /* Check if the user is the team creator */
            if ($user->id == $team->creator_id) {
                /* Grant all permissions to the creator */
                $data['manage_seat_settings'] = true;
                $data['cancel_subscription'] = true;
                $data['delete_seat'] = true;
            } else {
                /* Retrieve assigned seat for the member and the associated role */
                $assignedSeat = Assigned_Seat::where('member_id', $member->id)->where('seat_id', $seat->id)->first();
                $role = Role::find($assignedSeat->id);
                $permissions = Permission::whereIn('slug', [
                    'manage_seat_settings',
                    'cancel_subscription',
                    'delete_seat'
                ])->get();

                /* Check and assign permissions for the current user */
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

            /* Return the data as JSON */
            return response()->json($data);
        } catch (ModelNotFoundException $e) {
            /* Return a 404 response if any model (SeatInfo, AssignedSeats, Role) is not found */
            return response()->json(['success' => false, 'errors' => 'Seat Not Found'], 404);
        } catch (Exception $e) {
            /* Log the exception for debugging purposes */
            Log::error($e);

            /* Return a JSON response with the error message and a 404 status code */
            return response()->json(['success' => false, 'errors' => 'Something went wrong'], 500);
        }
    }

    /**
     * Update the name of the company associated with a seat.
     *
     * @param string $slug The unique identifier of the team.
     * @param int $seat_id The ID of the seat to update.
     * @param string $name The new name for the company.
     * @return \Illuminate\Http\JsonResponse JSON response indicating success or failure.
     */
    public function updateName($slug, $seat_id, $name)
    {
        try {
            /* Get the currently authenticated user */
            $user = Auth::user();

            /* Retrieve the team associated with the provided slug */
            $team = Team::where('slug', $slug)->first();

            /* Retrieve the seat associated with the team and seat ID */
            $seat = Seat::where('team_id', $team->id)->where('id', $seat_id)->firstOrFail();

            /* Initialize permission to manage seat settings */
            $access_manage_seat_settings = true;

            /* Check if the user is not the team creator */
            if ($user->id != $team->creator_id) {
                /* Retrieve the team member entry for the current user */
                $member = Team_Member::where('user_id', $user->id)->where('team_id', $team->id)->first();

                /* If the user is not a member or doesn't have an assigned seat, deny access */
                if (!$member || !Assigned_Seat::where('member_id', $member->id)->where('seat_id', $seat->id)->exists()) {
                    return response()->json(['success' => false, 'errors' => 'You do not have access to this seat'], 403);
                }

                /* Retrieve the assigned seat for the member */
                $assignedSeat = Assigned_Seat::where('member_id', $member->id)->where('seat_id', $seat->id)->first();

                /* Get the role of the assigned seat */
                $role = Role::find($assignedSeat->id);

                /* Retrieve the permission to manage seat settings */
                $permission = Permission::where('slug', 'manage_seat_settings')->first();

                /* Check if the role has permission to manage seat settings */
                $access_manage_seat_settings = Role_Permission::where('role_id', $role->id)
                    ->where('permission_id', $permission->id)
                    ->value('access') ?? false;
            }

            /* If the user has permission to manage the seat settings */
            if ($access_manage_seat_settings) {
                /* Retrieve the company info associated with the seat */
                $company_info = Company_Info::where('id', $seat->company_info_id)->first();

                /* Update the company name and timestamp */
                $company_info->name = $name;
                $company_info->updated_at = now();

                /* Save the updated company info */
                if ($company_info->save()) {
                    $seat->company_info = $company_info;

                    /* Return success response with the updated seat data */
                    return response()->json(['success' => true, 'seat' => $seat]);
                }

                /* If saving fails, return an error response */
                return response()->json(['success' => false, 'errors' => 'Seat Updation Failed'], 500);
            }

            /* If the user does not have permission to manage the seat, return a 403 error */
            return response()->json(['success' => false, 'errors' => 'You do not have permission to manage this seat'], 403);
        } catch (ModelNotFoundException $e) {
            /* Return a 404 response if any model (SeatInfo, AssignedSeats, Role) is not found */
            return response()->json(['success' => false, 'errors' => 'Seat Not Found'], 404);
        } catch (Exception $e) {
            /* Log the exception for debugging purposes */
            Log::error($e);

            /* Return a JSON response with the error message and a 404 status code */
            return response()->json(['success' => false, 'errors' => 'Something went wrong'], 500);
        }
    }
}
