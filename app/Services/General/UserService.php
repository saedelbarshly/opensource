<?php

namespace App\Services\General;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
class UserService
{
    public function getAll($request, $userType = null)
    {
        $query = User::query();

        if ($userType) {
            $query->where('user_type', $userType);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($request->has('is_active')) {
            $query->where('is_active', $request->is_active);
        }

        if ($request->has('is_banned')) {
            $query->where('is_banned', $request->is_banned);
        }

        $perPage = $request->input('perPage', 15);
        $page = $request->input('page', 1);

        if ($request->has('paginate')) {
            return $query->latest()->paginate($perPage, ['*'], 'page', $page);
        }

        return $query->latest()->get();
    }

    public function create(array $data, string $userType)
    {
        $data['user_type'] = $userType;

        $data['email_verified_at'] = now();
        $data['phone_verified_at'] = now();
        $data['is_active'] = $data['is_active'] ?? true;

        $user = User::create($data);

        return $user;
    }

    public function update(User $user, array $data)
    {
        if (isset($data['password']) && filled($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $user->update($data);

        return $user;
    }

    public function delete(User $user)
    {
        $user->delete();
        return true;
    }

    public function toggleActive(User $user)
    {
        $user->update(['is_active' => !$user->is_active]);
        $user->refresh();
        return $user;
    }
}
