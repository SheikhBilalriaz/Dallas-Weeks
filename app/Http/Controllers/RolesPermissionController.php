<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Team;
use App\Models\Role;
use App\Models\Permission;
use App\Models\Role_Permission;
use Illuminate\Support\Facades\Auth;
use Exception;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class RolesPermissionController extends Controller
{
    /**
     * Retrieve and prepare roles and permissions with their associated roles.
     *
     * @return \Illuminate\View\View The view with team and user data.
     */
    public function rolesPermission($slug)
    {
        /* Retrieve the team associated with the slug */
        $team = Team::where('slug', $slug)->first();

        /* Fetch all available permissions */
        $permissions = Permission::all();

        /* Fetch roles that either belong to the system (team_id = 0) or to the user's team */
        $roles = Role::whereIn('team_id', [0, $team->id])->get();

        /* Prepare data to be passed to the view */
        $data = [
            'title' => 'Roles & Permission',
            'team' => $team,
            'permissions' => $permissions,
            'roles' => $roles,
            'count_role' => Role::where('team_id', $team->id)->count(),
        ];

        /* Return the view with the prepared data */
        return view('dashboard.role_permission', $data);
    }

    /**
     * Create a new role and assign permissions based on the request data.
     *
     * @param \Illuminate\Http\Request $request The incoming HTTP request containing form data.
     * @return \Illuminate\Http\JsonResponse The JSON response indicating success or failure.
     */
    public function customRole($slug, Request $request)
    {
        try {
            /* Get all the input data from the request */
            $all = $request->all();

            /* Retrieve the currently authenticated user */
            $creator = Auth::user();

            /* Retrieve the team associated with the slug */
            $team = Team::where('slug', $slug)->first();

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
                'name' => $all['role_name'],
                'team_id' => $team->id,
                'creator_id' => $creator->id,
            ]);

            /* Remove 'role_name' and '_token' from the request data */
            unset($all['role_name']);
            unset($all['_token']);

            /* Fetch all available permissions */
            $permissions = Permission::all();

            /* Iterate over each permission to assign it to the new role */
            foreach ($permissions as $permission) {
                if (array_key_exists($permission['slug'], $all)) {
                    /* If the permission is present in the request, create a Role_Permission entry with access */
                    Role_Permission::create([
                        'role_id' => $role->id,
                        'permission_id' => $permission->id,
                        'access' => 1,
                        'view_only' => array_key_exists('view_only_' . $permission['slug'], $all) ? 1 : 0,
                    ]);
                } else {
                    /* If the permission is not present in the request, create a Role_Permission entry without access */
                    Role_Permission::create([
                        'role_id' => $role->id,
                        'permission_id' => $permission->id,
                        'access' => 0,
                        'view_only' => 0,
                    ]);
                }

                /* Remove processed permission entries from the request data */
                unset($all[$permission['slug']]);
                unset($all['view_only_' . $permission['slug']]);
            }

            /* Return the remaining data in JSON format */
            return response()->json(['success' => true]);
        } catch (Exception $e) {
            /* Log the exception for debugging purposes */
            Log::error($e->getMessage());

            /* Return a JSON response with the error message */
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function getRole($slug, $role_id)
    {
        try {
            /* Check if the role ID is not one of the default role IDs (1, 2, 3) */
            if ($role_id != 1 && $role_id != 2 && $role_id != 3) {
                /* Retrieve the role */
                $role = Role::find($role_id);

                $permissions_to_roles = Role_Permission::where('role_id', $role_id)->get();
                $permissions = Permission::all();

                /* Return a JSON response with the success */
                return response()->json([
                    'success' => true,
                    'role' => $role,
                    'permissions_to_roles' => $permissions_to_roles,
                    'permissions' => $permissions
                ]);
            }

            /* If the role ID is one of the default roles (1, 2, 3), throw an exception */
            throw new Exception('Cannot edit default roles');
        } catch (Exception $e) {
            /* Log the exception message for debugging purposes */
            Log::error($e->getMessage());

            /* Return a JSON response with the error message and failure status */
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function editRole($slug, $role_id, Request $request)
    {
        try {
            /* Get all the input data from the request */
            $all = $request->all();
            /* Check if the role ID is not one of the default role IDs (1, 2, 3) */
            if ($role_id != 1 && $role_id != 2 && $role_id != 3) {
                /* Retrieve the currently authenticated user */
                $user = Auth::user();

                /* Retrieve the team associated with the slug */
                $team = Team::where('slug', $slug)->first();

                /* Validate request data */
                $validator = Validator::make($request->all(), [
                    'role_name' => 'required|string|max:191',
                ]);

                /* Return validation errors if validation fails */
                if ($validator->fails()) {
                    return back()->withErrors($validator)
                        ->with('custom_role_edit_error', true)
                        ->withInput();
                }

                /* Create a new role for the team */
                $role = Role::where('team_id', $team->id)->first();
                $role->name = $all['role_name'];
                $role->updated_at = now();
                $role->save();

                /* Remove 'role_name' and '_token' from the request data */
                unset($all['role_name']);
                unset($all['_token']);

                /* Fetch all available permissions */
                $permissions = Permission::all();

                /* Iterate over each permission to assign it to the new role */
                foreach ($permissions as $permission) {
                    $rolePermission = Role_Permission::where('role_id', $role->id)->where('permission_id', $permission->id)->first();
                    if (array_key_exists($permission['slug'], $all)) {
                        /* If the permission is present in the request, create a Role_Permission entry with access */
                        $rolePermission->access = 1;
                        $rolePermission->view_only = array_key_exists('view_only_' . $permission['slug'], $all) ? 1 : 0;
                    } else {
                        /* If the permission is not present in the request, create a Role_Permission entry without access */
                        $rolePermission->access = 0;
                        $rolePermission->view_only = 0;
                    }
                    $rolePermission->save();

                    /* Remove processed permission entries from the request data */
                    unset($all[$permission['slug']]);
                    unset($all['view_only_' . $permission['slug']]);
                }

                /* Return the remaining data in JSON format */
                return response()->json(['success' => true]);
            }

            /* If the role ID is one of the default roles (1, 2, 3), throw an exception */
            throw new Exception('Cannot edit default roles');
        } catch (Exception $e) {
            /* Log the exception for debugging purposes */
            Log::error($e->getMessage());

            /* Return a JSON response with the error message */
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Delete a role by its ID.
     *
     * @param int $role_id The ID of the role to be deleted.
     * @return \Illuminate\Http\JsonResponse The JSON response indicating success or failure.
     */
    public function deleteRole($slug, $role_id)
    {
        try {
            /* Check if the role ID is not one of the default role IDs (1, 2, 3) */
            if ($role_id != 1 && $role_id != 2 && $role_id != 3) {
                /* Retrieve the currently authenticated user */
                $user = Auth::user();

                //TODO: Check if role is in use

                /* Delete role permissions associated with the role */
                Role_Permission::where('role_id', $role_id)->delete();

                /* Delete the role */
                Role::find($role_id)->delete();

                /* Return a JSON response with the success */
                return response()->json(['success' => true]);
            }

            /* If the role ID is one of the default roles (1, 2, 3), throw an exception */
            throw new Exception('Cannot delete default roles');
        } catch (Exception $e) {
            /* Log the exception message for debugging purposes */
            Log::error($e->getMessage());

            /* Return a JSON response with the error message and failure status */
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }
}
