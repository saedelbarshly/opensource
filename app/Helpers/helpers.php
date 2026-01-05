<?php

use App\Models\User;
use App\Models\Device;
use App\Enums\UserType;
use App\Models\Setting;
use App\Models\Equipment;
use App\Models\Permission;
use Illuminate\Support\Str;
use App\Models\Verification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Notification;

if (!function_exists('json')) {
    function json(mixed $data = null, ?string $message = '', string $status = 'success', int $headerStatus = 200): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'status'    => $status,
            'message'   => is_string($data) ? $data : $message,
            'data'      => is_string($data) ? null : $data,
        ], $headerStatus);
    }
}

if (!function_exists('setting')) {
    function setting($attr)
    {
        static $tableExists;

        if ($tableExists === null) {
            $tableExists = Schema::hasTable('settings');
        }

        if (!$tableExists) return false;

        $phone = $attr;

        if ($attr == 'phone') {
            $attr = 'phones';
        }

        $setting = Setting::where('key', $attr)->first() ?? [];

        if ($attr == 'project_name') {
            return !empty($setting) ? $setting->value : 'Aaleyat';
        }

        if ($attr == 'vat') {
            return !empty($setting) ? (int) $setting->value : 15;
        }

        if ($attr == 'cancellation_fee') {
            return !empty($setting) ? (int) $setting->value : 20;
        }

        if ($attr == 'online') {
            return !empty($setting) ? $setting->value : true;
        }

        if ($attr == 'wallet') {
            return !empty($setting) ? $setting->value : true;
        }

        if ($attr == 'cash') {
            return !empty($setting) ? $setting->value : true;
        }

        if ($attr == 'app_tax') {
            return !empty($setting) ? (int) $setting->value : 5;
        }

        if ($attr == 'cash_fees') {
            return !empty($setting) ? (int) $setting->value : 2;
        }

        if ($attr == 'scheduled_period') { // order scheduled period or will cancel hours
            return !empty($setting) ? (int) $setting->value : 24;
        }
        if ($attr == 'activation_period')  // driver activation period or will cancel minutes
        {
            return !empty($setting) ? (int) $setting->value : 30;
        }
        if ($attr == 'offer_price_period') // accept or reject offer price period or will cancel minutes
        {
            return !empty($setting) ? (int) $setting->value : 30;
        }

        if ($attr == 'paid_period') {  // order paid period or will cancel hours
            return !empty($setting) ? (int) $setting->value : 24;
        }
        if ($attr == 'rate_period') {  // order paid period or will cancel minutes
            return !empty($setting) ? (int) $setting->value : 10;
        }

        if ($attr == 'confirm_period') // client confirm period or auto confirm munites
        {
            return !empty($setting) ? (int) $setting->value : 30;
        }


        if ($attr == 'logo') {
            return !empty($setting) ? asset('storage/images/setting') . "/" . $setting->value : asset('dashboardAssets/images/icons/logo_sm.png');
        }

        if ($phone == 'phone') {
            return !empty($setting) && $setting->value ? json_decode($setting->value)[0] : null;
        } elseif ($phone == 'phones') {
            return !empty($setting) && $setting->value ? implode(",", json_decode($setting->value)) : null;
        }

        if (!empty($setting)) {
            return $setting->value;
        }

        return false;
    }
}

if (!function_exists('getDateRange')) {
    function getDateRange($request): array
    {
        $duration = $request->input('duration', 'month');
        $inputDate = $request->input('date');

        $date = $inputDate
            ? (strlen($inputDate) === 7 ? Carbon::createFromFormat('Y-m', $inputDate) : Carbon::parse($inputDate))
            : now();

        $ranges = [
            'day' => [
                'start' => $date->copy()->format('Y-m-d'),
                'end'   => $date->copy()->format('Y-m-d'),
            ],
            'week' => [
                'start' => $date->copy()->startOfWeek(Carbon::SATURDAY)->format('Y-m-d'),
                'end'   => $date->copy()->endOfWeek(Carbon::FRIDAY)->format('Y-m-d'),
            ],
            'month' => [
                'start' => $date->copy()->startOfMonth()->format('Y-m-d'),
                'end'   => $date->copy()->endOfMonth()->format('Y-m-d'),
            ],
            'year' => [
                'start' => $date->copy()->startOfYear()->format('Y-m-d'),
                'end'   => $date->copy()->endOfYear()->format('Y-m-d'),
            ],
        ];

        $range = $ranges[$duration] ?? $ranges['month'];

        return [
            'from_date' => $request->input('from_date') ?? $range['start'],
            'to_date'   => $request->input('to_date') ?? $range['end'],
        ];
    }
}

if (!function_exists('getDateRangeFilter')) {
    function getDateRangeFilter($duration = 'month', $from = null, $to = null,$inputDate = null): array
    {
        $date = $inputDate
            ? (strlen($inputDate) === 7 ? Carbon::createFromFormat('Y-m', $inputDate) : Carbon::parse($inputDate))
            : now();

        $ranges = [
            'day' => [
                'start' => $date->copy()->format('Y-m-d'),
                'end'   => $date->copy()->format('Y-m-d'),
            ],
            'tomorrow' => [
                'start' => $date->copy()->tomorrow()->format('Y-m-d'),
                'end'   => $date->copy()->tomorrow()->format('Y-m-d'),
            ],
            'week' => [
                'start' => $date->copy()->startOfWeek(Carbon::SATURDAY)->format('Y-m-d'),
                'end'   => $date->copy()->endOfWeek(Carbon::FRIDAY)->format('Y-m-d'),
            ],
            'month' => [
                'start' => $date->copy()->startOfMonth()->format('Y-m-d'),
                'end'   => $date->copy()->endOfMonth()->format('Y-m-d'),
            ],
            'year' => [
                'start' => $date->copy()->startOfYear()->format('Y-m-d'),
                'end'   => $date->copy()->endOfYear()->format('Y-m-d'),
            ],
        ];

        $range = $ranges[$duration] ?? $ranges['month'];

        return [
            'from' => ($from ?? $range['start']) . ' 00:00:00',
            'to'   => ($to ?? $range['end']) . ' 23:59:59',
        ];
    }
}

if (!function_exists('getPeriodRange')) {
    function getPeriodRange(string $period): array
    {
        switch ($period) {
            case 'week':
                $currentStart = now()->startOfWeek();
                $currentEnd = now()->endOfWeek();

                $prevStart = now()->subWeek()->startOfWeek();
                $prevEnd = now()->subWeek()->endOfWeek();
                break;

            case 'year':
                $currentStart = now()->startOfYear();
                $currentEnd = now()->endOfYear();

                $prevStart = now()->subYear()->startOfYear();
                $prevEnd = now()->subYear()->endOfYear();
                break;

            case 'month':
            default:
                $currentStart = now()->startOfMonth();
                $currentEnd = now()->endOfMonth();

                $prevStart = now()->subMonth()->startOfMonth();
                $prevEnd = now()->subMonth()->endOfMonth();
                break;
        }

        return [$currentStart, $currentEnd, $prevStart, $prevEnd];
    }

}

if (!function_exists('generateUniqueUsername')) {
    function generateUniqueUsername($name)
    {
        $baseUsername = Str::slug($name, '_'); // Convert name to a slug format
        $username = $baseUsername;
        $count = 1;

        while (User::where('username', $username)->exists()) {
            $username = $baseUsername . '_' . $count;
            $count++;
        }

        return $username;
    }
}

if (!function_exists('extractPrefixFromAction')) {
    function extractPrefixFromAction(string $action): ?string
    {
        if (str_contains($action, '@')) {
            [$controller] = explode('@', $action);
            $parts = explode('\\', $controller);
            return $parts[5] ?? null;
        }

        return null;
    }
}

if (!function_exists('generateOtp')) {
    function generateOtp($length)
    {
        if (!app()->isProduction()) {
            return str_repeat('1', $length);
        }
        do {
            $code = '';
            for ($i = 0; $i < $length; $i++) {
                $code .= rand(0, 9);
            }
            $exists = Verification::where('code', $code)->exists();
        } while ($exists);

        return $code;
    }
}


if (!function_exists('generatePassword')) {
    function generatePassword() {
        $word = ucfirst(strtolower(substr(str_shuffle('abcdefghijklmnopqrstuvwxyz'), 0, 6)));
        $symbols = ['@', '#', '$', '%', '&', '*', '!'];
        $symbol = $symbols[array_rand($symbols)];
        $number = rand(100, 999);

        return app()->isProduction() ? $word . $symbol . $number : "123456789";
    }
}

if (!function_exists('createDevice')) {
    function createDevice($request, $user): void
    {
        try {
            $agent_token = $request->header('agent_token');
            Device::updateOrCreate(
                [
                    'agent_token' => $agent_token,
                    'user_id' => $user->id
                ],
                [
                    'device_token' => $request->device_token,
                    'device_type' => $request->device_type,
                ]
            );

        } catch (\Exception $exception) {
            Log::error($exception);
        }
    }
}

if (!function_exists('generateVerificationToken')) {
    function generateVerificationToken($type = null): string
    {
        return Str::uuid();
    }
}

if (!function_exists('log_activity')) {
    function log_activity(string $class, $data, string $level = 'info'): void
    {
        $separator = str_repeat('=', 120);

        $formattedData = is_array($data) || is_object($data)
            ? json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
            : (string) $data;

        if (in_array($level, ['info', 'debug', 'warning', 'error', 'critical'], true)) {
            Log::$level($separator);
            Log::$level("$class => $formattedData");
            Log::$level($separator);
        } else {
            Log::info($separator);
            Log::info("$class => $formattedData");
            Log::info($separator);
        }
    }
}


if (!function_exists('permissions_names')) {
    function permissions_names()
    {
        $user = auth('api')->user();
        $prefix = $user->role()->first()->prefix;
        $all = Permission::where('prefix', $prefix)->pluck('back_route_name')->toArray();
        $permissions_names = [];
        foreach ($all as $item) {
            $arr = explode('.', $item);
            $permissions_names[] = $arr[0];
        }
        $permissions_names = array_unique($permissions_names);

        return $permissions_names;
    }
}


if (!function_exists('distance')) {
    function distance($startLat, $startLng, $endLat, $endLng, $unit = "K")
    {
        // $unit = M --> Miles
        // $unit = K --> Kilometers
        // $unit = N --> Nautical Miles

        $startLat = (float) $startLat;
        $startLng = (float) $startLng;
        $endLat = (float) $endLat;
        $endLng = (float) $endLng;

        $theta = $startLng - $endLng;
        $dist = sin(deg2rad($startLat)) * sin(deg2rad($endLat)) + cos(deg2rad($startLat)) * cos(deg2rad($endLat)) * cos(deg2rad($theta));
        $dist = acos($dist);
        $dist = rad2deg($dist);
        $miles = $dist * 60 * 1.1515;
        $unit = strtoupper($unit);

        if ($unit == "K") {
            return ($miles * 1.609344);
        } else if ($unit == "N") {
            return ($miles * 0.8684);
        } else {
            return $miles;
        }
    }
}


if (!function_exists('calcPriceForTransport')) {
    function calcPriceForTransport($equipment, $distance): float|int
    {
        return $equipment->prices->first()->base_price * $distance;
    }
}


if (!function_exists('calcAppTax')) {
    function calcAppTax($amount): float
    {
        return ($amount * setting('app_tax')) / 100;
    }
}


if(!function_exists('refundAmount')) {
    function refundAmount($amount): float
    {
        return ($amount - ($amount * (setting('cancellation_fee') / 100)));
    }
}



if (!function_exists('notifySafely'))
{
    function notifySafely($notifiables, $notification): void
    {
        if ($notifiables) {
            Notification::send($notifiables, $notification);
        }
//        $notifiables = is_array($notifiables) ? $notifiables : [$notifiables];
//
//        $notifiables = array_filter($notifiables);
//
//        if (empty($notifiables)) {
//            return;
//        }
//
//
//        Notification::send($notifiables, $notification);
    }

}




