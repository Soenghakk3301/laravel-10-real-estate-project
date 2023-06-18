<?php

namespace App\Http\Controllers\Backend;

use App\Exports\PermissionExport;
use App\Http\Controllers\Controller;
use App\Imports\PermissionImport;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Facades\Excel;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function allPermission()
    {
        $permissions = Permission::all();
        return view('backend.pages.permission.all_permission', compact('permissions'));
    }

    public function addPermission()
    {
        return view('backend.pages.permission.add_permission');
    }

    public function storePermission(Request $request)
    {
        $permission = Permission::create([
           'name' => $request->name,
           'group_name' => $request->group_name,
         ]);

        $notification = array(
          'message' => 'Permission Create Successfully',
          'alert-type' => 'success'
          );

        return redirect()->route('all.permission', compact('permission'))->with($notification);
    }

    public function editPermission($id)
    {
        $permission = Permission::findOrFail($id);
        return view('backend.pages.permission.edit_permission', compact('permission'));

    }

    public function updatePermission(Request $request)
    {
        $per_id = $request->id;

        Permission::findOrFail($per_id)->update([
            'name' => $request->name,
            'group_name' => $request->group_name,
        ]);

        $notification = array(
          'message' => 'Permission Updated Successfully',
          'alert-type' => 'success'
      );

        return redirect()->route('all.permission')->with($notification);
    }

    public function deletePermission($id)
    {
        Permission::findOrFail($id)->delete();

        $notification = array(
          'message' => 'Permission Deleted Successfully',
          'alert-type' => 'success'
        );

        return redirect()->back()->with($notification);

    }

    public function importPermission()
    {
        return view('backend.pages.permission.import_permission');
    }

    public function export()
    {
        return Excel::download(new PermissionExport(), 'permission.xlsx');
    }

    public function import(Request $request)
    {
        Excel::import(new PermissionImport(), $request->file('import_file'));

        $notification = array(
         'message' => 'Permission Imported Successfully',
         'alert-type' => 'success'
         );

        return redirect()->back()->with($notification);
    }

    public function allRoles()
    {
        $roles = Role::all();
        return view('backend.pages.roles.all_roles', compact('roles'));
    }

    public function addRoles()
    {
        return view('backend.pages.roles.add_roles');
    }

    public function storeRoles(Request $request)
    {
        Role::create([
           'name' => $request->name,
        ]);

        $notification = array(
         'message' => 'Role Create Successfully',
         'alert-type' => 'success'
         );

        return redirect()->route('all.roles')->with($notification);
    }

    public function editRoles($id)
    {
        $roles = Role::findOrFail($id);
        return view('backend.pages.roles.edit_roles', compact('roles'));
    }

    public function updateRoles(Request $request)
    {
        $role_id = $request->id;

        Role::findOrFails($role_id)->update([
            'name' => $request->name,
        ]);

        $notification = array(
          'message' => 'Role Updated Successfully',
          'alert-type' => 'success'
         );

        return redirect()->route('all.roles')->with($notification);
    }

    public function deleteRoles($id)
    {
        Role::findOrFail($id)->delete();

        $notification = array(
           'message' => 'Role Deleted Successfully',
           'alert-type' => 'success'
         );

        return redirect()->back()->with($notification);
    }

    public function addRolesPermission()
    {
        $roles = Role::all();
        $permission = Permission::all();
        $permission_groups = User::getpermissionGroups();

        return view('backend.pages.rolesetup.add_roles_permission', compact('roles', 'permission', 'permission_groups'));
    }

    public function rolePermissionStore(Request $request)
    {
        $data = array();
        $permissions = $request->permission;

        foreach($permissions as $key => $item) {
            $data['role_id'] = $request->role_id;
            $data['permission_id'] = $item;

            DB::table('role_has_permissions')->insert($data);
        }

        $notification = array(
         'message' => 'Role Permission Added Successfully',
         'alert-type' => 'success'
         );

        return redirect()->route('all.roles.permission')->with($notification);
    }

    public function allRolesPermission()
    {
        $roles = Role::all();
        return view('backend.pages.rolesetup.all_roles_permission', compact('roles'));
    }

    public function adminEditRoles($id)
    {
        $role = Role::findOrFail($id);
        $permission = Permission::all();
        $permission_group = User::getPermissionGroups();
        return view('backend.pages.rolesetup.edit_roles_permission', compact('role', 'permission', 'permission_groups'));
    }


    public function adminRolesUpdate(Request $request, $id)
    {

        $role = Role::findOrFail($id);
        $permissions = $request->permission;

        if (!empty($permissions)) {
            $role->syncPermissions($permissions);
        }

        $notification = array(
            'message' => 'Role Permission Updated Successfully',
            'alert-type' => 'success'
        );

        return redirect()->route('all.roles.permission')->with($notification);

    }

    public function adminDeleteRoles($id)
    {
        $role = Role::findOrFail($id);
        if (!is_null($role)) {
            $role->delete();
        }

        $notification = array(
            'message' => 'Role Permission Deleted Successfully',
            'alert-type' => 'success'
        );

        return redirect()->back()->with($notification);
    }

    public function allAdmin()
    {
        $alladmin = User::where('role', 'admin')->get();
        return view('backend.pages.admin.all_admin', compact('alladmin'));
    }

    public function addAdmin()
    {
        $roles = Role::all();
        return view('backend.pages.admin.add_admin', compact('roles'));
    }

    public function storeAdmin(Request $request)
    {
        $user = new User();
        $user->username = $request->username;
        $user->name = $request->name;
        $user->email = $request->email;
        $user->phone = $request->phone;
        $user->address = $request->address;
        $user->password =  Hash::make($request->password);
        $user->role = 'admin';
        $user->status = 'active';
        $user->save();

        if ($request->roles) {
            $user->assignRole($request->roles);
        }

        $notification = array(
                'message' => 'New Admin User Inserted Successfully',
                'alert-type' => 'success'
            );

        return redirect()->route('all.admin')->with($notification);
    }

    public function editAdmin($id)
    {
        $user = User::findOrFail($id);
        $roles = Role::all();
        return view('backend.pages.admin.edit_admin', compact('user', 'roles'));
    }

    public function updateAdmin(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $user->username = $request->username;
        $user->name = $request->name;
        $user->email = $request->email;
        $user->phone = $request->phone;
        $user->address = $request->address;
        $user->role = 'admin';
        $user->status = 'active';
        $user->save();

        $user->roles()->detach();
        if ($request->roles) {
            $user->assignRole($request->roles);
        }

        $notification = array(
                'message' => 'New Admin User Updated Successfully',
                'alert-type' => 'success'
            );

        return redirect()->route('all.admin')->with($notification);
    }

    public function deleteAdmin($id)
    {
        $user = User::findOrFail($id);
        if (!is_null($user)) {
            $user->delete();
        }

        $notification = array(
                'message' => 'New Admin User Deleted Successfully',
                'alert-type' => 'success'
            );

        return redirect()->back()->with($notification);
    }
}