<?php

namespace App\Http\Controllers;
use App\Helpers\FlashMsg;
use App\Http\Controllers\Controller;

use App\Admin;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class AdminRoleManageController extends Controller
{
    const BASE_PATH ='backend.admin-role-manage.';
    public function __construct()
    {

        $this->middleware(['auth:admin','role:Super Admin']);

    }
    public function new_user(){
        $roles = Role::pluck('name','name')->all();
        return view(self::BASE_PATH.'add-new-user',compact('roles'));
    }
    public function new_user_add(Request $request){
        $this->validate($request,[
            'name' => 'required|string|max:191',
            'username' => 'required|string|max:191|unique:admins',
            'email' => 'required|email|max:191',
            'role' => 'required|string|max:191',
            'image' => 'nullable|string',
            'password' => 'required|min:8|confirmed',
            'description' => 'nullable|string',
            'designation' => 'nullable|string',
        ]);
        $admin = Admin::create([
            'name' => $request->name,
            'username' => $request->username,
            'email' => $request->email,
            'image' => $request->image,
            'password' => Hash::make($request->password),
            'description' => $request->description,
            'designation' => $request->designation,
        ]);
        $admin->assignRole($request->role);
        return redirect()->back()->with(['msg' => __('New Admin Added'),'type' =>'success' ]);
    }

    public function all_user(){
        $all_user = Admin::all()->except(Auth::id());
        return view(self::BASE_PATH.'all-user')->with(['all_user' => $all_user]);
    }
    public function user_edit($id){
        $admin = Admin::findOrFail($id);
        $roles = Role::pluck('name','name')->all();
        $adminRole = $admin->roles->pluck('name','name')->all();
        return view(self::BASE_PATH.'edit-user',compact('roles','adminRole','admin'));
    }
    public function user_update(Request $request){
        $this->validate($request,[
            'name' => 'required|string|max:191',
            'email' => 'required|email|max:191',
            'role' => 'required|string|max:191',
            'image' => 'nullable|string',
             'description' =>'nullable',
            'designation' => 'nullable',
        ]);
        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'image' => $request->image,
             'description' => $request->description,
            'designation' => $request->designation,
        ];

        $admin = Admin::findOrFail($request->user_id);

        // Never let the last Super Admin be demoted — it would lock everyone
        // out of role management.
        if ($admin->hasRole('Super Admin') && $request->role !== 'Super Admin'
            && Admin::role('Super Admin')->where('id','!=',$admin->id)->count() === 0){
            return redirect()->back()->with(['msg' => __('You cannot remove the last Super Admin. Assign the Super Admin role to another admin first.'),'type' =>'danger' ]);
        }

        $admin->update($data);
        DB::table('model_has_roles')->where('model_id',$admin->id)->delete();
        $admin->assignRole($request->role);

        return redirect()->back()->with(['msg' => __('Admin Details Updated'),'type' =>'success' ]);
    }
    public function new_user_delete(Request $request,$id){
        $admin = Admin::findOrFail($id);

        if ($admin->hasRole('Super Admin') && Admin::role('Super Admin')->count() <= 1){
            return redirect()->back()->with(['msg' => __('You cannot delete the last Super Admin.'),'type' =>'danger' ]);
        }

        $admin->delete();
        return redirect()->back()->with(['msg' => __('Admin Deleted'),'type' =>'danger' ]);
    }
    public function user_password_change(Request $request){
        $this->validate($request, [
            'password' => 'required|string|min:8|confirmed'
        ]);
        $user = Admin::findOrFail($request->ch_user_id);
        $user->password = Hash::make($request->password);
        $user->save();
        return redirect()->back()->with(['msg'=> __('Password Change Success..'),'type'=> 'success']);

    }

    // ============================================== Admin Role Codes ==========================================

    public function all_admin_role(){
        $roles = Role::all();
        return view(self::BASE_PATH.'role.index',compact('roles'));
    }

    public function new_admin_role_index(){
        $permissionGroups = $this->grouped_permissions();
        return view(self::BASE_PATH.'role.create',compact('permissionGroups'));
    }

    public function store_new_admin_role(Request $request){
        $this->validate($request,[
            'name' => 'required|string|max:191|unique:roles,name',
            'permission' => 'required|array|min:1',
        ],[
            'permission.required' => __('Please select at least one permission for this role.'),
            'permission.min' => __('Please select at least one permission for this role.'),
        ]);
        $role = Role::create(['name' => $request->name,'guard_name' => 'admin']);
        $role->syncPermissions($request->permission);
        return back()->with(FlashMsg::settings_update('New Role Created'));
    }

    public function edit_admin_role($id){
        $role = Role::findOrFail($id);
        $permissionGroups = $this->grouped_permissions();
        $rolePermissions = DB::table("role_has_permissions")->where("role_has_permissions.role_id",$id)
            ->pluck('role_has_permissions.permission_id','role_has_permissions.permission_id')
            ->all();
        return view(self::BASE_PATH.'role.edit',compact('role','permissionGroups','rolePermissions'));
    }

    public function update_admin_role(Request $request){
        $this->validate($request,[
            'name' => 'required|string|max:191',
            'permission' => 'required|array|min:1',
        ],[
            'permission.required' => __('Please select at least one permission for this role.'),
            'permission.min' => __('Please select at least one permission for this role.'),
        ]);
        $role = Role::find($request->id);
        $role->name = $request->input('name');
        $role->save();
        $role->syncPermissions($request->permission);

        return back()->with(FlashMsg::settings_update('Role Updated'));
    }

    public function delete_admin_role($id){
        $role = Role::findOrFail($id);

        if ($role->name === 'Super Admin'){
            return back()->with(['msg' => __('The Super Admin role cannot be deleted.'),'type' => 'danger']);
        }

        if (DB::table('model_has_roles')->where('role_id',$role->id)->exists()){
            return back()->with(['msg' => __('This role is assigned to one or more admins. Reassign those admins before deleting it.'),'type' => 'danger']);
        }

        $role->delete();
        return back()->with(FlashMsg::settings_delete('Role Deleted'));
    }

    /**
     * Group every permission under a human label (config order), with any
     * permission not listed in the config falling into an "Other" group so
     * nothing is ever hidden from the role screen.
     *
     * @return array<string,\Illuminate\Support\Collection>
     */
    protected function grouped_permissions(){
        $all = Permission::orderBy('name')->get()->keyBy('name');
        $groups = [];
        $used = [];

        foreach (config('admin_permissions.groups', []) as $label => $names){
            $items = collect($names)
                ->map(fn ($name) => $all->get($name))
                ->filter()
                ->values();
            foreach ($names as $name){ $used[$name] = true; }
            if ($items->isNotEmpty()){
                $groups[$label] = $items;
            }
        }

        $others = $all->reject(fn ($permission) => isset($used[$permission->name]))->values();
        if ($others->isNotEmpty()){
            $groups[__('Other')] = $others;
        }

        return $groups;
    }
}
