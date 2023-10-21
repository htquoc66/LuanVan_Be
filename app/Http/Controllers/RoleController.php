<?php

namespace App\Http\Controllers;
use Validator;
use App\Models\Role;

use App\Models\Permission;
use App\Models\PermissionRole;
use Illuminate\Http\Request;

class RoleController extends Controller
{

    public function index()
    {
        $roles = Role::all();
        return response()->json($roles);
    }


    public function store(Request $request)
    {
        $validatedData =  $request->validate([
            'name' => 'required|string', 
        ],
        [
            'name.required' => 'Trường tên vai trò là bắt buộc.',
        ]);    
      
        try {
            $role = Role::create($validatedData);
            return response()->json(['success' => true, 'id' => $role->id]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Đã xảy ra lỗi khi tạo khách hàng', 'error' => $e->getMessage()], 500);
        }
    }


    public function show($id)
    {
        $role = Role::with('permissions')->find($id);

        if (!$role) {
            return response()->json(['error' => 'Role not found'], 404);
        }
        return response()->json($role);   
    }


    public function update(Request $request, $id)
    {
        $role = Role::find($id);

        if (!$role) {
            return response()->json(['error' => 'Role not found'], 404);
        }

        $validatedData =  $request->validate([
            'name' => 'required|string', 
        ],
        [
            'name.required' => 'Trường tên vai trò là bắt buộc.',
        ]);

        try{
            $role->update( $validatedData);
            return response()->json(['success' => true], 200);
        
        } catch(\Exception $e){
            return response()->json(['success' => false, 
            'message' => 'Đã xảy ra lỗi khi cập nhật vai trò',
             'error' => $e->getMessage()], 500);
        }
    }


    public function destroy($id)
    {
        $role = Role::find($id);

        if (!$role) {
            return response()->json(['error' => 'Role not found'], 404);
        }

        $role->delete();
        return response()->json(['success' => true], 200);
    }

    public function getPermissions(){
        $permissions = Permission::all();
        return response()->json($permissions);
    }

    
    public function addPermissionToRole(Request $request, $idRole)
    {
        $permissions = $request->input('permissions');   

        foreach($permissions as $value){
            $permissionRole = new PermissionRole;
            $permissionRole->permission_id = $value;
            $permissionRole->role_id = $idRole;
            $permissionRole->save();
        }           
        return response()->json(['success' => true]);
    }

    public function updatePermissionToRole(Request $request, $idRole)
    {
        $permissions = $request->input('permissions');
        // Xóa tất cả các quyền của role cũ trước khi thêm quyền mới
        PermissionRole::where('role_id', $idRole)->delete();
        // Thêm quyền mới cho role
        foreach($permissions as $value){
            $permissionRole = new PermissionRole;
            $permissionRole->permission_id = $value;
            $permissionRole->role_id = $idRole;
            $permissionRole->save();
        }     
        
        return response()->json(['success' => true]);
    }
}
