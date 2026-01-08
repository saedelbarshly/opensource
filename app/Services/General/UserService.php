<?php

namespace App\Services\General;

use App\Models\User;
use App\Enums\UserType;
use App\Filter\UserFilter;

class UserService
{
    public function list(UserFilter $filter, UserType $type)
    {
        return User::where('user_type', $type)
            ->filter($filter)
            ->orderByDesc('created_at')
            ->when(
                request('filters.perPage'),
                fn($q) => $q->paginate(request('filters.perPage')),
                fn($q) => $q->get()
            );
    }

    public function show($id, UserType $type, array $relations = []): User
    {
        return User::where('user_type', $type)
            ->with($relations)
            ->findOrFail($id);
    }

    public function create(array $data): User
    {
        return User::create($data + [
            'email_verified_at' => now(),
            'phone_verified_at' => now(),
            'is_active' => true
        ]);
    }

    public function update(User $user, array $data): User
    {
        if (isset($data['password']) && !filled($data['password'])) {
            unset($data['password']);
        }

        $user->update($data);

        return $user;
    }

    public function delete(User $user): bool
    {
        return $user->delete();
    }

    public function toggle(User $user, string $field): User
    {
        if (in_array($field, ['is_active', 'is_banned'])) {
            $user->update([
                $field => ! $user->{$field}
            ]);
        }

        return $user->refresh();
    }
}
