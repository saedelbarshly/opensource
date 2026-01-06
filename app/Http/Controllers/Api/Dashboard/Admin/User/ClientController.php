<?php

namespace App\Http\Controllers\Api\Dashboard\Admin\User;

use App\Enums\UserType;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\BaseApiController;
use App\Services\General\UserService;
use App\Http\Resources\Api\General\ShowProfileResource;
use App\Http\Requests\Api\Dashboard\Admin\Auth\ClientRequest;

class ClientController extends BaseApiController
{
    private UserService $userService;

    public function __construct(UserService $userService)
    {
        parent::__construct(User::class, ShowProfileResource::class, ShowProfileResource::class, ClientRequest::class);
        $this->userService = $userService;
    }

    public function index()
    {
        $users = $this->userService->getAll(request(), UserType::CLIENT->value);
        return $this->successResponse($users);
    }

    public function store()
    {
        $request = resolve(ClientRequest::class);
        $user = $this->userService->create($request->validated(), UserType::CLIENT->value);
        return $this->successResponse(ShowProfileResource::make($user), 'Client created successfully');
    }

    public function update($id)
    {
        $request = resolve(ClientRequest::class);
        $user = User::findOrFail($id);
        $user = $this->userService->update($user, $request->validated());
        return $this->successResponse(ShowProfileResource::make($user), 'Client updated successfully');
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $this->userService->delete($user);
        return $this->successResponse([], 'Client deleted successfully');
    }
}
