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

class ClientController extends Controller
{
    public function __construct(
        private readonly UserService $userService
    ) {}

    public function index(UserFilter $userFilter): JsonResponse
    {
        $clients = $this->userService->list($userFilter, UserType::CLIENT);

        ListUserResource::wrap('clients');

        if ($clients instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator) {
            return json(
                ListUserResource::collection($clients)
                    ->response()
                    ->getData(true),
                trans('Clients retrieved successfully')
            );
        }

        return json(ListUserResource::collection($clients), trans('Clients retrieved successfully'));
    }

    public function show($id): JsonResponse
    {
        $client = $this->userService->show($id, UserType::CLIENT, ['roles', 'permissions']);

        return json(ShowProfileResource::make($client), trans('Client retrieved successfully'));
    }

    public function store(UserRequest $request): JsonResponse
    {
        $client = $this->userService->create($request->validated() + ['user_type' => UserType::CLIENT->value]);

        return json(ShowProfileResource::make($client), trans('Client created successfully'));
    }

    public function update(UserRequest $request, User $user): JsonResponse
    {
        $client = $this->userService->update($user, $request->validated());
        return json(ShowProfileResource::make($client), trans('Client updated successfully'));
    }

    public function destroy(User $user): JsonResponse
    {
        $this->userService->delete($user);
        return json([], 'Client deleted successfully');
    }

    public function toggle(User $user, string $field): JsonResponse
    {
        $client = $this->userService->toggle($user, $field);
        return json(ShowProfileResource::make($client), trans('Client status updated successfully'));
    }
}
