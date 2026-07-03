<?php

namespace App\Http\Controllers;

use App\Http\Resources\EditRolePermissionResource;
use App\Http\Resources\getAllRolePermissionResource;
use App\Models\RolePermission;
use Illuminate\Http\Request;

class RolePermissionController extends Controller
{


   public function getAllRolePermissions(){
    $rolePermissions = RolePermission::whereNotIn('id', [1])->get();
    $rp = getAllRolePermissionResource::collection($rolePermissions);
    return response()->json(['success' => true, 'data' => $rp]);
     
   }
    public function saveAndUpdateRolePermissions(Request $request) {

        $is_chk = false;
        
        $rolePermissions = RolePermission::where('id', $request->id)->first();
        if(!$rolePermissions){
            $is_chk = true;
            $rolePermissions = new RolePermission();
        }
        $rolePermissions->role = $request->role;
        $rolePermissions->permissions = json_encode($request->permissions);
        $rolePermissions->save();
        if($is_chk){
            return response()->json(['success' => true, 'message' => 'Role and Permissions created successfully!']);
        }else{
            return response()->json(['success' => true, 'message' => 'Role and Permissions updated successfully!']);
        }
    }

   public function editRolePermissions(Request $request){
    $rolePermissions = RolePermission::where('id', $request->id)->first();
    if($rolePermissions){
       $rp = new EditRolePermissionResource($rolePermissions);
       return response()->json(['success' => true, 'data' => $rp]);
    }else{
        return response()->json(['success' => false, 'data' => '']);
    }

    }

    public function deleteRolePermissions(Request $request) {
     $rolePermissions = RolePermission::where('id', $request->id)->first();
     if($rolePermissions){
        $rolePermissions->delete();
        return response()->json(['success' => true, 'msg' => 'Role and Permissions deleted successfully!']);
      }else{
        return response()->json(['success' => true, 'msg' => 'Role and Permissions not found!']);
      }
    }
}
