<?php

namespace App\Http\Controllers\Api\Dashboard\Admin\Setting;


use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Dashboard\Admin\Setting\SettingRequest;
use App\Http\Resources\Api\Dashboard\Admin\Setting\SettingResource;
use App\Models\Setting;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SettingController extends Controller
{
    public function index(Request $request)
    {
        $settings = Setting::get();
        return json(SettingResource::collection($settings), status: 'success', headerStatus: 200);
    }
    public function store(SettingRequest $request)
    {
        DB::beginTransaction();
        try {
            $setting = Setting::latest()->get();
            $inputs  = $request->validated();

            foreach ($inputs as $key => $value) {
                Setting::updateOrCreate(['key' => trim($key)], ['value' => $value]);
            }
            DB::commit();
            return json(SettingResource::collection($setting->fresh()),message: trans('Created successfully'), status: 'success', headerStatus: 200);
        } catch (Exception $e) {
            DB::rollback();
            return json(null,message: trans('Something went wrong, please try again'), status: 'fail', headerStatus: 422);
        }
    }
}
