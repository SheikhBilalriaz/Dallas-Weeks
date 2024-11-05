<?php

namespace App\Http\Controllers;

use App\Models\Assigned_Seat;
use Illuminate\Http\Request;
use App\Models\Team;
use App\Models\Role;
use App\Models\Permission;
use App\Models\Role_Permission;
use App\Models\Team_Member;
use Illuminate\Support\Facades\Auth;
use Exception;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class RolesPermissionController extends Controller
{
    /**
     * Retrieve and prepare roles and permissions for the team.
     *
     * @param string $slug The team slug used to identify the team.
     * @return \Illuminate\View\View The view containing the team, roles, and permissions data.
     */
    public function rolesPermission($slug)
    {
        try {
            /* Retrieve the team associated with the slug */
            $team = Team::where('slug', $slug)->first();

            /* Fetch all available permissions */
            $permissions = Permission::all();

            /* Fetch roles that either belong to the system (team_id = 0) or to the user's team */
            $roles = Role::whereIn('team_id', [0, $team->id])->get();

            /* Count the number of roles that are specifically associated with the current team */
            $count_role = $roles->where('team_id', $team->id)->count();

            /* Prepare data to be passed to the view */
            $data = [
                'title' => 'Roles & Permission',
                'team' => $team,
                'permissions' => $permissions,
                'roles' => $roles,
                'count_role' => $count_role,
            ];

            /* Return the view with the prepared data */
            return view('dashboard.role_permission', $data);
        } catch (Exception $e) {
            /* Log the exception for debugging purposes */
            Log::error($e);

            /* Return a JSON response with the error message and a 404 status code */
            return redirect()->route('dashboardPage', ['slug' => $slug])
                ->withErrors(['error' => 'Something went wrong']);
        }
    }

    /**
     * Create a new role and assign permissions based on the request data.
     *
     * @param string $slug The team slug used to identify the team.
     * @param \Illuminate\Http\Request $request The incoming HTTP request containing form data.
     * @return \Illuminate\Http\RedirectResponse Redirects with success or error messages.
     */
    public function customRole($slug, Request $request)
    {
        try {
            /* Retrieve the currently authenticated user */
            $creator = Auth::user();

            /* Retrieve the team associated with the provided slug */
            $team = Team::where('slug', $slug)->first();

            /* Check if the team already has more than 10 roles */
            $count_role = Role::where('team_id', $team->id)->count();
            if ($count_role >= 10) {
                return redirect()
                    ->route('rolesPermissionPage', ['slug' => $team->slug])
                    ->withErrors(['error' => 'You cannot add more than 10 roles for this team.']);
            }

            /* Validate request data */
            $validator = Validator::make($request->all(), [
                'role_name' => 'required|string|max:191',
            ]);

            /* Return validation errors if validation fails */
            if ($validator->fails()) {
                return back()->withErrors($validator)
                    ->with('custom_role_error', true)
                    ->withInput();
            }

            /* Create a new role for the team */
            $role = Role::create([
                'name' => $request->input('role_name'),
                'team_id' => $team->id,
            ]);

            /* Retrieve all permissions from the database */
            $permissions = Permission::all();

            /* Process each permission to assign them to the role based on request data */
            foreach ($permissions as $permission) {
                /* Check if permission exists in the request and whether it's view-only */
                $hasAccess = $request->has($permission->slug);
                $viewOnly = $request->has('view_only_' . $permission->slug);

                /* Create Role_Permission entry based on the user's selection */
                Role_Permission::create([
                    'role_id' => $role->id,
                    'permission_id' => $permission->id,
                    'access' => $hasAccess ? 1 : 0,
                    'view_only' => $viewOnly && $hasAccess ? 1 : 0,
                ]);
            }

            /* Return success message */
            return redirect()
                ->route('rolesPermissionPage', ['slug' => $team->slug])
                ->with(['success' => 'New role created successfully']);
        } catch (Exception $e) {
            /* Log the exception for debugging purposes */
            Log::error($e);

            /* Return a JSON response with the error message */
            return redirect()
                ->route('rolesPermissionPage', ['slug' => $team->slug])
                ->withErrors(['error' => 'Something went wrong']);
        }
    }

    /**
     * Retrieve the role and its associated permissions based on the role ID.
     *
     * @param string $slug The team slug used to identify the team.
     * @param int $role_id The ID of the role to retrieve.
     * @return \Illuminate\Http\JsonResponse The JSON response containing role data or error information.
     */
    public function getRole($slug, $role_id)
    {
        try {
            /* Retrieve the team associated with the provided slug */
            $team = Team::where('slug', $slug)->first();

            /* Retrieve the role by ID */
            $role = Role::where('id', $role_id)->where('team_id', $team->id)->first();

            /* If the role doesn't exist, return a JSON response with the error message */
            if (!$role) {
                return response()->json(['success' => false, 'error' => 'Role not found'], 404);
            }

            /* Retrieve the permissions associated with this role */
            $permissions_to_roles = Role_Permission::where('role_id', $role_id)->get();

            /* Retrieve all available permissions */
            $permissions = Permission::all();

            /* Return the role and permission details in a successful JSON response */
            return response()->json([
                'success' => true,
                'role' => $role,
                'permissions_to_roles' => $permissions_to_roles,
                'permissions' => $permissions
            ]);
        } catch (Exception $e) {
            /* Log the exception message for debugging purposes */
            Log::error($e);

            /* Return a JSON response with the error message and failure status */
            return response()->json(['success' => false, 'error' => 'Something went wrong'], 500);
        }
    }

    /**
     * Edit an existing role and assign permissions based on the request data.
     *
     * @param string $slug The team slug used to identify the team.
     * @param int $role_id The ID of the role to retrieve.
     * @param \Illuminate\Http\Request $request The incoming HTTP request containing form data.
     * @return \Illuminate\Http\RedirectResponse Redirects with success or error messages.
     */
    public function editRole($slug, $role_id, Request $request)
    {
        try {
            /* Retrieve the team associated with the provided slug */
            $team = Team::where('slug', $slug)->first();

            /* Validate request data */
            $validator = Validator::make($request->all(), [
                'edit_role_name' => 'required|string|max:191',
            ]);

            /* Return validation errors if validation fails */
            if ($validator->fails()) {
                return back()->withErrors($validator)
                    ->with(['custom_role_edit_error' => true, 'role_id' => $role_id])
                    ->withInput();
            }

            /* Create a new role for the team */
            $role = Role::where('id', $role_id)->where('team_id', $team->id)->first();

            if (!$role) {
                /* Return a JSON response with the error message */
                return redirect()
                    ->route('rolesPermissionPage', ['slug' => $team->slug])
                    ->withErrors(['error' => 'Role not found']);
            }

            $role->name = $request->input('edit_role_name');
            $role->updated_at = now();
            $role->save();

            /* Fetch all available permissions */
            $permissions = Permission::all();

            /* Iterate over each permission to assign it to the new role */
            foreach ($permissions as $permission) {
                $rolePermission = Role_Permission::where('role_id', $role->id)->where('permission_id', $permission->id)->first();

                /* Check if permission exists in the request and whether it's view-only */
                $hasAccess = $request->has($permission->slug);
                $viewOnly = $request->has('view_only_' . $permission->slug);

                $rolePermission->access = $hasAccess ? 1 : 0;
                $rolePermission->view_only = $viewOnly && $hasAccess ? 1 : 0;
                $rolePermission->save();
            }

            /* Redirect with success message */
            return redirect()
                ->route('rolesPermissionPage', ['slug' => $team->slug])
                ->with(['success' => 'Role edited successfully']);
        } catch (Exception $e) {
            /* Log the exception for debugging purposes */
            Log::error($e);

            /* Return a redirect with an error message */
            return redirect()
                ->route('rolesPermissionPage', ['slug' => $team->slug])
                ->withErrors(['error' => 'Something went wrong']);
        }
    }

    /**
     * Delete a role by its ID.
     *
     * @param string $slug The team slug used to identify the team.
     * @param int $role_id The ID of the role to be deleted.
     * @return \Illuminate\Http\JsonResponse The JSON response indicating success or failure.
     */
    public function deleteRole($slug, $role_id)
    {
        try {
            /* Retrieve the team by its slug */
            $team = Team::where('slug', $slug)->first();

            /* Retrieve the role by its ID and the team ID */
            $role = Role::where('id', $role_id)->where('team_id', $team->id)->first();

            /* If the role doesn't exist, throw an exception */
            if (!$role) {
                return response()->json(['success' => false, 'error' => 'Role not found'], 404);
            }

            /* Find the team member associated with the current user and team */
            $members = Team_Member::where('team_id', $team->id)->get();

            /* Check if the role is assigned to any member in the team */
            $assignedSeats = Assigned_Seat::whereIn('member_id', $members->pluck('id')->toArray())->where('role_id', $role->id)->get();

            /* If the role is already in use (assigned to a member), throw an exception */
            if ($assignedSeats->isNotEmpty()) {
                return response()->json(['success' => false, 'error' => 'Role is already in use'], 500);
            }

            /* Delete all associated role-permission records before deleting the role */
            Role_Permission::where('role_id', $role->id)->get()->each(function ($permission) {
                $permission->delete();
            });

            /* Delete the role */
            $role->delete();

            /* Return a success JSON response */
            return response()->json(['success' => true]);
        } catch (Exception $e) {
            /* Log the exception message for debugging purposes */
            Log::error($e);

            /* Return a failure response with the exception message */
            return response()->json(['success' => false, 'error' => 'Something went wrong'], 500);
        }
    }
}
