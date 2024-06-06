<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserPermission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PermissionController extends Controller
{
    public function addPermission(Request $request)
    {

        try {

            $rules = array(
                'requester_id' => 'required',
                'team_member_id' => 'required',
            );

            $validator = Validator::make($request->all(), $rules);


            if ($validator->fails()) {
                return $this->simpleReturn('error', $validator->errors(), 400);
            }

            $permissionExist = UserPermission::where('requester_id', $request->requester_id)
                ->where('team_member_id', $request->team_member_id)->first();

            if ($permissionExist) {
                return $this->simpleReturn('error','Permission already requested', 400);
            }


            $user_permission = new UserPermission();
            $user_permission->requester_id = $request->input('requester_id');
            $user_permission->team_member_id = $request->input('team_member_id');
            $user_permission->permission_type = 'edit';
            $user_permission->save();

            return $this->simpleReturn('success', 'Permission added successfully', 200);
        } catch (\Throwable $th) {
            return $this->simpleReturn('error', $th->getMessage(), 404);
        }
    }

    public function getPermission(Request $request)
    {
        try {

            $user = User::where('token', $request->token)->first('id');

            // Get pending permission requests send to logged in user
            $userPermissions = UserPermission::with('requester')
                ->select('requester_id', 'team_member_id', 'permission_type')
                ->where('team_member_id', $user->id)
                ->where('status', 2)->get();   // Status 2 :Pending

            if (!$userPermissions->isEmpty()) {
                return $this->simpleReturn('success', $userPermissions, 200);
            }

            return $this->simpleReturn('error', 'No Records found', 404);
        } catch (\Throwable $th) {
            return $this->simpleReturn('error', $th->getMessage(), 404);
        }
    }

    public function updateStatus(Request $request)
    {


        // Validate the request data
        $request->validate([
            'id' => 'required',
            'status' => 'required',
        ]);

        // Find the UserPermission record by ID
        $permission = UserPermission::find($request->id);

        if (!$permission) {
            return $this->simpleReturn('error', 'Permission not found', 404);
        }

        $status = $request->status == 1 ? 'Approved' : 'Denied';

        // Update the permission status
        $permission->update(['status' => $request->status]);

        // Return a response
        return $this->simpleReturn('success', "Permission {$status} successfully", 200);
    }
}
