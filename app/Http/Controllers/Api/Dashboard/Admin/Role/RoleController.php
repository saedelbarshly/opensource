<?php

namespace App\Http\Controllers\Api\Dashboard\Admin\Role;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Dashboard\Admin\Role\RoleRequest;
use App\Http\Resources\Api\Dashboard\Admin\Role\RoleResource;
use App\Http\Resources\Api\Dashboard\Admin\Role\RoleDetailsResource;
use App\Models\Role;

class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::paginate('10');
        RoleResource::wrap('roles');
        return json(RoleResource::collection($roles)->response()->getData(true),trans('Role has been received successfully'),'success',200);
    }

    public function list(){
        $roles = Role::all();
        return json(RoleResource::collection($roles),trans('Role has been received successfully'),'success',200);
    }

    public function show($id){
        $role = Role::query()->with('permissions')->findOrFail($id);
        return json(RoleDetailsResource::make($role),trans('Role has been received successfully'),'success',200);
    }
    public function store(RoleRequest $request)
    {
        $role = Role::create($request->validated());
        $role->permissions()->sync(request()->permission_ids);
        return json(RoleResource::make($role),trans('Added successfully'),'success',200);
    }
    public function update(RoleRequest $request, $id)
    {
        $role = Role::findOrFail($id);
        $role->update($request->validated());
        $role->permissions()->sync(request()->permission_ids);
    }

    public function destroy($id)
    {
        $role = Role::findOrFail($id);
        if ($role->users->count() > 0) {
            return json(null,trans('This role cannot be deleted because the user has used it'),'error',422);
        }
        $role->delete();
        return json(null,trans('Deleted successfully'),'success',200);
    }
}
