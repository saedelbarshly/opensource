<?php

namespace App\Http\Requests\Api\Dashboard\Admin\Role;

use App\Http\Requests\ApiMasterRequest;

class RoleRequest extends ApiMasterRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $rules = [
            'prefix' => 'required|string|in:admin',
            "permission_ids" =>"required|array" ,
            "permission_ids.*"  => "exists:permissions,id"
        ];
        foreach (config('translatable.locales') as $locale) {
            $rules[$locale . '.name'] = 'required|string|between:3,250';
        }
        return $rules;
    }
}
