<?php

namespace App\Http\Requests\Api\Dashboard\Admin\Coupon;

use App\Enums\CouponApplyOn;
use App\Enums\CouponType;
use App\Http\Requests\ApiMasterRequest;
use App\Models\Coupon;
use Illuminate\Validation\Rule;

class CouponRequest extends ApiMasterRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $status = isset($this->coupon) ? 'nullable' : 'required';
        $rules = [
            // 'apply_on'                => ['required',Rule::in(CouponApplyOn::values())],
            'company_id'              => ['sometimes',Rule::exists('companies','id')],
            'code'                    => [$status, 'string', 'max:20', Rule::unique('coupons', 'code')->ignore($this->coupon->id ?? null)],
            'type'                    => ['required', 'string', Rule::in(CouponType::values())],
            'value'                   => ['required', 'numeric', 'min:1', Rule::when($this->type === 'percentage', 'max:100')],
            'min_order_total'         => [Rule::requiredIf($this->type == CouponType::FIXED),'numeric','gt:value','min:100','max:1000000'],
            'max_order_total'         => [Rule::requiredIf($this->type == CouponType::PERCENTAGE),'min:100','max:1000000'],
            'start_at' => [
                'required',
                Rule::when($this->isMethod('POST'), 'after_or_equal:today'),
                // function ($attribute, $value, $fail) {
                //     if ($this->coupon) {
                //         $coupon = Coupon::find($this->coupon->id);
                //         $oldStartAt = optional($coupon)->start_at;
                //         if ($oldStartAt && $value < $oldStartAt->toDateString()) {
                //             if ($oldStartAt && $value < $oldStartAt->toDateString()) {
                //                 $fail('The start date cannot be earlier than the current start date.');
                //             }
                //         }
                //     }
                // },
            ],
            'end_at'                  => ['required', 'date', 'after:start_at'],
            'limit'                   => ['required', 'numeric', 'min:2'],
            'limit_for_user'          => ['required', 'numeric', 'min:1','lte:limit'],
            'is_active'               => ['required', 'boolean'],
        ];
        foreach (config('translatable.locales') as $locale) {
            $rules[$locale . '.name']          = $status . '|string|between:2,100';
            $rules[$locale . '.description']   = 'sometimes|string|between:2,1000';
        }

        return $rules;
    }
}
