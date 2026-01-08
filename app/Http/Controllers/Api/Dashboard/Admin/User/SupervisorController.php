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

class SupervisorController extends Controller
{
    public function __construct(
        private readonly UserService $userService
    ) {}

    public function index(UserFilter $userFilter): JsonResponse
    {
        $supervisors = $this->userService->list($userFilter, UserType::ADMIN);

        ListUserResource::wrap('supervisors');

        if ($supervisors instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator) {
            return json(
                ListUserResource::collection($supervisors)
                    ->response()
                    ->getData(true),
                trans('Supervisors retrieved successfully')
            );
        }

        return json(ListUserResource::collection($supervisors), trans('Supervisors retrieved successfully'));
    }

    public function show($id): JsonResponse
    {
        $supervisor = $this->userService->show($id, UserType::ADMIN, ['roles', 'permissions']);

        return json(ShowProfileResource::make($supervisor), trans('Supervisor retrieved successfully'));
    }

    public function store(UserRequest $request): JsonResponse
    {
        $supervisor = $this->userService->create($request->validated() + ['user_type' => UserType::ADMIN->value]);

        return json(ShowProfileResource::make($supervisor), trans('Supervisor created successfully'));
    }

    public function update(UserRequest $request, User $user): JsonResponse
    {
        $supervisor = $this->userService->update($user, $request->validated());
        return json(ShowProfileResource::make($supervisor), trans('Supervisor updated successfully'));
    }

    public function destroy(User $user): JsonResponse
    {
        $this->userService->delete($user);
        return json([], 'Supervisor deleted successfully');
    }

    public function toggle(User $user, string $field): JsonResponse
    {
        $supervisor = $this->userService->toggle($user, $field);
        return json(ShowProfileResource::make($supervisor), trans('Supervisor status updated successfully'));
    }
}
