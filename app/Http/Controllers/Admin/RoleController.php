<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateRoleRequest;
use App\Http\Requests\UpdateRoleRequest;
use App\Models\Permission;
use App\Models\Role;
use Inertia\Inertia;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use App\Queries\Filters\FilterMultipleFields;

class RoleController extends Controller
{
    public function index(): \Inertia\Response
    {
        $this->authorize('viewAny', Role::class);

        $perPage = request()->input('perPage', 10);
        if ($perPage > 100) {
            $perPage = 100;
        }

        $roles = QueryBuilder::for(Role::class)->with('permissions:id,name')->withCount('users')
            ->allowedFilters([
                'id',
                'name',
                'created_at',
                'updated_at',
                'weight',
                'is_staff',
                'is_hidden_from_staff_list',
                'display_name',
                AllowedFilter::custom('q', new FilterMultipleFields(['name', 'display_name', 'id']))
            ])
            ->allowedSorts(['id', 'name', 'created_at', 'updated_at', 'weight', 'is_staff', 'is_hidden_from_staff_list', 'display_name'])
            ->defaultSort('-weight')
            ->paginate($perPage)
            ->withQueryString();

        return Inertia::render('Admin/Role/IndexRole', [
            'roles' => $roles,
            'filters' => request()->all(['perPage', 'sort', 'filter']),
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
            ->with(['toast' => ['type' => 'success', 'title' => __('Created Successfully'), 'body' => __('New Role is created successfully')]]);
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
            ->with(['toast' => ['type' => 'success', 'title' => __('Updated Successfully'), 'body' => __('Role updated successfully')]]);
    }

    public function destroy(Role $role)
    {
        $this->authorize('delete', $role);

        if ($role->name == Role::SUPER_ADMIN_ROLE_NAME || $role->name == Role::DEFAULT_ROLE_NAME) {
            return redirect()->back()->with(['toast' => ['type' => 'danger', 'title' => __('Action Failed'), 'body' => __(':role role cannot be deleted!', ['role' => $role->display_name])]]);
        }

        // Don't delete if there is any user attached to this role
        if ($role->users()->count() > 0) {
            return redirect()->back()->with(['toast' => ['type' => 'danger', 'title' => __('Action Failed'), 'body' => __(':role cannot be deleted because there are users on this role.!', ['role' => $role->display_name])]]);
        }

        $role->delete();
        return redirect()->route('admin.role.index')
            ->with(['toast' => ['type' => 'success', 'title' => __('Deleted Successfully'), 'body' => __('Role has been deleted!')]]);
    }
}