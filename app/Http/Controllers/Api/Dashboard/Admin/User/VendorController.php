<?php

namespace App\Http\Controllers\Api\Dashboard\Admin\User;

use App\Models\User;
use App\Enums\UserType;
use App\Filter\UserFilter;
use App\Http\Controllers\Controller;
use App\Services\General\UserService;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Http\Requests\Api\General\User\UserRequest;
use App\Http\Resources\Api\General\ListUserResource;
use App\Http\Resources\Api\General\ShowProfileResource;

class VendorController extends Controller
{
    public function __construct(
        private readonly UserService $userService
    ) {}

    public function index(UserFilter $userFilter): JsonResponse
    {
        $vendors = $this->userService->list($userFilter, UserType::VENDOR);

        ListUserResource::wrap('vendors');

        if ($vendors instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator) {
            return json(
                ListUserResource::collection($vendors)
                    ->response()
                    ->getData(true),
                trans('Vendors retrieved successfully')
            );
        }

        return json(ListUserResource::collection($vendors), trans('Vendors retrieved successfully'));
    }

    public function show($id): JsonResponse
    {
        $vendor = $this->userService->show($id, UserType::VENDOR, ['roles', 'permissions']);

        return json(ShowProfileResource::make($vendor), trans('Vendor retrieved successfully'));
    }

    public function store(UserRequest $request): JsonResponse
    {
        $vendor = $this->userService->create($request->validated() + ['user_type' => UserType::VENDOR->value]);

        return json(ShowProfileResource::make($vendor), trans('Vendor created successfully'));
    }

    public function update(UserRequest $request, User $user): JsonResponse
    {
        $vendor = $this->userService->update($user, $request->validated());
        return json(ShowProfileResource::make($vendor), trans('Vendor updated successfully'));
    }

    public function destroy(User $user): JsonResponse
    {
        $this->userService->delete($user);
        return json([], 'Vendor deleted successfully');
    }

    public function toggle(User $user, string $field): JsonResponse
    {
        $vendor = $this->userService->toggle($user, $field);
        return json(ShowProfileResource::make($vendor), trans('Vendor status updated successfully'));
    }
}
