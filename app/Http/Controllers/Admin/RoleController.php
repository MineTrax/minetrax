<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateRoleRequest;
use App\Http\Requests\UpdateRoleRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Permission;
use App\Models\Role;

class RoleController extends Controller
{
    public function index(): \Inertia\Response
    {
        $this->authorize('viewAny', Role::class);

        $roles = Role::query()
            ->with('permissions:id,name')
            ->withCount('users')
            ->orderByDesc('weight')->paginate(10);

        return Inertia::render('Admin/Role/IndexRole', [
            'roles' => $roles
        ]);
    }

    public function create(): \Inertia\Response
    {
        $this->authorize('create', Role::class);

        $permissions = Permission::pluck('name');

        return Inertia::render('Admin/Role/CreateRole', [
            'permissions' => $permissions
        ]);
    }

    public function store(CreateRoleRequest $request)
    {
        $role = Role::create([
            'name' => $request->name,
            'display_name' => $request->display_name,
            'color' => $request->color ?? null,
            'is_staff' => $request->is_staff,
            'is_hidden_from_staff_list' => $request->is_hidden_from_staff_list,
            'web_message_format' => $request->web_message_format,
            'weight' => $request->weight
        ]);

        $role->givePermissionTo($request->permissions);

        $role->addMediaFromRequest('photo')->toMediaCollection('role');

        return redirect()->route('admin.role.index')
            ->with(['toast' => ['type' => 'success', 'title' => 'Created Successfully', 'body' => 'New Role is created successfully']]);
    }

    public function show(Role $role)
    {
        //
    }

    public function edit(Role $role)
    {
        $this->authorize('update', $role);

        $permissions = Permission::pluck('name');
        $role['permissions'] = $role->permissions()->pluck('name');

        return Inertia::render('Admin/Role/EditRole', [
            'permissions' => $permissions,
            'role' => $role
        ]);
    }

    public function update(UpdateRoleRequest $request, Role $role)
    {
        $this->authorize('update', $role);

        $role->display_name = $request->display_name;
        $role->color = $request->color ?? null;
        $role->is_staff = $request->is_staff;
        $role->is_hidden_from_staff_list = $request->is_hidden_from_staff_list;
        $role->weight = $request->weight;
        $role->web_message_format = $request->web_message_format;
        $role->save();
        $role->syncPermissions($request->permissions);

        // If there is photo upload photo
        if ($request->photo) {
            $role->addMediaFromRequest('photo')->toMediaCollection('role');
        }

        // Redirect to listing page
        return redirect()->route('admin.role.index')
            ->with(['toast' => ['type' => 'success', 'title' => 'Updated Successfully', 'body' => 'Role updated successfully']]);
    }

    public function destroy(Role $role)
    {
        $this->authorize('delete', $role);

        if ($role->name == Role::SUPER_ADMIN_ROLE_NAME || $role->name == Role::DEFAULT_ROLE_NAME) {
            return redirect()->back()->with(['toast' => ['type' => 'danger', 'title' => 'Action Failed', 'body' => $role->display_name.' role cannot be deleted!']]);
        }

        // Dont delete if there is any user attached to this role
        if ($role->users()->count() > 0) {
            return redirect()->back()->with(['toast' => ['type' => 'danger', 'title' => 'Action Failed', 'body' => $role->display_name.' cannot be deleted because there are users on this role.!']]);
        }

        $role->delete();
        return redirect()->route('admin.role.index')
            ->with(['toast' => ['type' => 'success', 'title' => 'Deleted Successfully', 'body' => 'Role has been deleted!']]);
    }
}
