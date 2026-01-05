<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use App\Models\Permission;
use Illuminate\Support\Str;
use Illuminate\Database\Seeder;
use App\Models\PermissionTranslation;
use Illuminate\Support\Facades\Route;
use App\Services\General\TranslationService;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissionsData = [];
        $routeCollection = Route::getRoutes();

        $patterns = [
            '.index'    => ['suffix' => '/show-all', 'name' => 'get all '],
            '.store'    => ['suffix' => '/add', 'name' => 'add '],
            '.show'     => ['suffix' => '/show', 'name' => 'show '],
            '.update_'  => ['suffix' => '/edit', 'name' => 'update '],
            '.update'   => ['suffix' => '/edit', 'name' => 'update '],
            '.destroy'  => ['suffix' => '/delete', 'name' => 'delete '],
            '.get'      => ['suffix' => '/show', 'name' => 'show '],
        ];

        foreach ($routeCollection as $index => $route) {
            $action = $route->getActionName();
            $routeName = $route->getName();

            if (!$routeName || !Str::startsWith($action, 'App\Http\Controllers\Api\Dashboard')) {
                continue;
            }

            if (Str::contains($routeName, ['profile', 'permission'])) {
                continue;
            }

            $used = false;
            foreach ($patterns as $pattern => $data) {
                if (Str::contains($routeName, $pattern)) {
                    $used = true;
                    $trimmed = Str::replace($pattern, '', $routeName);
                    $permissionsData[$index] = [
                        'back_name'     => $routeName,
                        'front_name'    => $trimmed . $data['suffix'],
                        'name'          => $data['name'] . $trimmed,
                        'prefix' => extractPrefixFromAction($action ?? '') ?? 'Admin',
                    ];
                    break;
                }
            }

            if (!$used) {
                $permissionsData[$index] = [
                    'back_name'     => $routeName,
                    'front_name'    => $routeName,
                    'name'          => $routeName,
                    'prefix' => extractPrefixFromAction($action ?? '') ?? 'Admin',
                ];
            }
        }

        foreach ($permissionsData as $perm) {
            $permission = Permission::withoutGlobalScope('prefix')->firstOrCreate([
                'front_route_name' => $perm['front_name'],
                'back_route_name' => $perm['back_name'],
                'prefix' => $perm['prefix'],
            ]);

            foreach (config('translatable.locales') as $locale) {
                $name = $this->transformRouteName($perm['name']);

                PermissionTranslation::firstOrCreate(
                    [
                        'locale' => $locale,
                        'permission_id' => $permission->id,
                    ],
                    [
                        'name' => $locale !== 'en'
                            ? (new TranslationService())->apiTranslate($name, $locale)
                            : $name,
                    ]
                );
            }
        }
        $this->createRoles();
    }

    private function transformRouteName(string $routeName): string
    {
        return collect(explode('.', $routeName))
            ->map(fn($part) => ucfirst($part))
            ->implode(' ');
    }

    private function createRoles()
    {
        $admins    = User::$ADMINS ?? ['Admin'];
        foreach ($admins as $admin) {
            $permission_ids = Permission::withoutGlobalScope('prefix')->where('prefix', $admin)->pluck('id')->toArray();
            if (empty($permission_ids)) continue;
            $role = Role::whereTranslation('name', lcfirst($admin), 'en')->first();
            if (! $role) {
                $data = ['en' => ['name' => lcfirst($admin)]];
                foreach (config('translatable.locales') as $locale) {
                    if ($locale != 'en') $data[$locale] = ['name' => (new TranslationService())->apiTranslate(lcfirst($admin), $locale)];
                }
                $role = Role::create($data + ['prefix' => strtolower($admin)]);
                $role->permissions()->sync($permission_ids);
            }
        }
    }
}
