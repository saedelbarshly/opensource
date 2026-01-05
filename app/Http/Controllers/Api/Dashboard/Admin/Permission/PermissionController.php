<?php

namespace App\Http\Controllers\Api\Dashboard\Admin\Permission;

use App\Enums\UserType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Dashboard\Admin\Permission\PermissionRequest;
use App\Http\Resources\Api\Dashboard\Admin\Permission\PermissionResource;
use App\Http\Resources\Api\Dashboard\Admin\Permission\PermissionDetailsResource;
use App\Models\Permission;

class PermissionController extends Controller
{
    public function index()
    {
        $permissions = Permission::where('prefix', 'admin')->paginate(10);
        PermissionDetailsResource::wrap('permissions');
        return json(PermissionDetailsResource::collection($permissions)->response()->getData(true),trans('Retrieved successfully'), status: 'success', headerStatus: 200);
    }
    public function list()
    {
        $dataArr = [];
        foreach (permissions_names() as $item) {
            $dataArr[trans('translation.permissions.' . $item)] = PermissionResource::collection(Permission::where('back_route_name', 'like', $item . '%')->get());
        }
        return json($dataArr,trans('Retrieved successfully'), status: 'success', headerStatus: 200);
    }

    public function sideBarPermissions()
    {
        $user = auth('api')->user();
        if ($user->user_type == UserType::SUPER_ADMIN) {
            $permissions = Permission::where('prefix', 'admin')->pluck('back_route_name')->toArray();
        } else {
            $permissions = $user->role ? $user->role()->first()->permissions()->pluck('back_route_name')->toArray() : [];
        }
        $permissions = $user->getPermissions($permissions);
        return json($permissions, status: 'success', headerStatus: 200);
    }

    public function show($id)
    {
        $permission = Permission::find($id);
        return json(PermissionDetailsResource::make($permission),trans('Retrieved successfully'), status: 'success', headerStatus: 200);
    }

    public function update(PermissionRequest $request, $id)
    {
        $permission = Permission::find($id);
        $permission->update($request->validated());
        return json(PermissionDetailsResource::make($permission), status: 'success', headerStatus: 200);
    }

}
