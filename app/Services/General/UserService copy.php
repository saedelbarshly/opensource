<?php

namespace App\Services\General;

use App\Models\User;
use App\Enums\UserType;
use App\Filter\UserFilter;
use Illuminate\Support\Facades\DB;
class UserService
{
    public function __construct(
    ) {}
    public function list(UserFilter $filter, UserType $type)
    {
        return User::where('user_type', $type)
            ->where('is_super', false)
            ->filter($filter)
            ->orderByDesc('created_at')
            ->paginate(15);
    }

    public function show($id, UserType $type, array $relations = []): User
    {
        return User::where('user_type', $type)
            ->where('is_super', false)
            ->with($relations)
            ->findOrFail($id);
    }

    public function create(array $data): User
    {
        return DB::transaction(function () use ($data) {
            $user = User::create($data + [
                'email_verified_at' => now(),
                'phone_verified_at' => now(),
                'is_active' => true
            ]);

            $this->handleVendorData($user, $data);

            return $user;
        });
    }

    public function update(User $user, array $data): User
    {
        return DB::transaction(function () use ($user, $data) {
            if (isset($data['password']) && !filled($data['password'])) {
                unset($data['password']);
            }

            $user->update($data);

            $this->handleVendorData($user, $data);

            return $user;
        });
    }

    protected function handleVendorData(User $user, array $data): void
    {
        if ($user->user_type !== UserType::VENDOR) {
            return;
        }

        $profile = $user->profile()->firstOrNew(['user_id' => $user->id]);

        $profile->commercial_register_number = $data['commercial_register_number'] ?? null;

        foreach (config('translatable.locales') as $locale) {
            $profile->translateOrNew($locale)->fill(
                data_get($data, $locale, [])
            );
        }

        $profile->save();


        if (isset($data['location'])) {
            $user->locations()->updateOrCreate(
                ['user_id' => $user->id],
                $data['location']
            );
        }
        if (isset($data['category_ids'])) {
            $user->specialties()->sync($data['category_ids']);
        }
    }

    public function delete(User $user): bool
    {
        return $user->delete();
    }

    public function toggle(User $user, string $field): User
    {
        // Map friendly field names to database column names
        $fieldMap = [
            'active' => 'is_active',
            'banned' => 'is_banned',
        ];

        if (isset($fieldMap[$field])) {
            $dbField = $fieldMap[$field];
            $user->update([
                $dbField => ! $user->{$dbField}
            ]);
        }

        return $user->refresh();
    }
}
