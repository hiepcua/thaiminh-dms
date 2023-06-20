<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Repositories\Permission\PermissionRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionController extends Controller
{
    protected $repository;

    public function __construct(PermissionRepositoryInterface $repository)
    {
        $this->repository = $repository;
        $this->middleware('can:xem_danh_sach_quyen')->only('index');
        $this->middleware('can:them_quyen')->only('store');
        $this->middleware('can:sua_quyen')->only('edit', 'update');
        $this->middleware('can:xoa_quyen')->only('destroy');
    }

    public function index(Request $request)
    {
        $permissions = $this->repository->paginate(20, ['roles']);

        return view('pages.permissions.index', compact('permissions'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'perm_name'  => ['required'],
            'perm_group' => ['required'],
            'perm_roles' => ['required'],
        ]);
        $perm_name  = $request->input('perm_name');
        $perm_group = $request->input('perm_group');
        $perm_roles = $request->input('perm_roles');
        $roles      = Role::query()->whereIn('id', $perm_roles)->get();

        $permission = $this->repository->create([
            'name'       => Str::snake($perm_name),
            'name_2'     => $perm_name,
            'group'      => $perm_group,
            'guard_name' => 'web'
        ]);
        $permission->syncRoles($roles);

        return redirect(route('admin.permissions.index'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     */
    public function edit(int $id)
    {
        $roles            = Role::query()->get();
        $permission       = Permission::query()->with('roles')->where('id', $id)->first();
        $permission_roles = $permission->roles->pluck('id')->toArray();
//        dd($permission_roles);

        return view('pages.permissions.add-edit', compact('permission', 'roles', 'permission_roles'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     */
    public function update(Request $request, int $id)
    {
        $request->validate([
            'perm_name'  => ['required'],
            'perm_group' => ['required'],
//            'perm_roles' => ['required'],
        ]);

        $perm_name  = $request->input('perm_name');
        $perm_group = $request->input('perm_group');
        $perm_roles = $request->input('perm_roles');

        $permission = Permission::query()->where('id', $id)->first();
        $permission->update([
            'name_2' => $perm_name,
            'group'  => $perm_group,
        ]);
        $permission->syncRoles($perm_roles);

        return redirect(route('admin.permissions.index'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     */
    public function destroy($id)
    {
        $permission = Permission::query()->where('id', $id)->first();
        $permission->delete();

        app()->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

        return redirect(route('admin.permissions.index'));
    }
}
