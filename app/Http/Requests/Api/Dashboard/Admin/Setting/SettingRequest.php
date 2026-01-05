<?php

namespace App\Http\Requests\Api\Dashboard\Admin\Setting;

use App\Http\Requests\ApiMasterRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class SettingRequest extends ApiMasterRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        if ($this->instructions) {
            $this->validateInstructions();
        }

        return [
            "phone"                         => "nullable|numeric",
            "email"                         => "nullable|string",
            "facebook"                      => "nullable|url",
            "twitter"                       => "nullable|url",
            "youtube"                       => "nullable|url",
            "instagram"                     => "nullable|url",
            "snapchat"                      => "nullable|url",
            "whatsapp"                      => "nullable|string|max:250",
            'whatsapp_link'                 => 'nullable|active_url',

            'lat'                           => ['sometimes','nullable'],
            'lng'                           => ['sometimes','nullable'],
            'address'                       => ['sometimes','nullable'],

            'app_tax'                       => 'required|numeric|min:1|max:99',
            'cancellation_fee'              => 'required|numeric|min:1|max:99',
            'scheduled_period'              => 'required|numeric|min:1|max:99',
            'activation_period'             => 'required|numeric|min:1|max:120',
            'offer_price_period'            => 'required|numeric|min:1|max:120',
            'paid_period'                   => 'required|numeric|min:1|max:48',
            'rate_period'                   => 'required|numeric|min:1|max:99',
            'confirm_period'                => 'required|numeric|min:1|max:120',

            'can_cancel_before_start_with_fee'    => 'required|boolean',
            'can_cancel_after_start_with_fee'     => 'required|boolean',
            'can_cancel_after_arrive'             => 'required|boolean',
        ];
    }

    protected function validateInstructions()
    {
        $requiredLanguages = config('translatable.locales');

        foreach ($requiredLanguages as $lang) {
            $this->validateLanguagePresent($lang);
            $this->validateLanguageNotEmpty($lang);
        }

        $this->validateLanguageConsistency();
    }

    protected function validateLanguagePresent(string $lang)
    {
        if (!isset($this->instructions[$lang])) {
            $this->throwValidationError("instructions {$lang} is required");
        }
    }

    protected function validateLanguageNotEmpty(string $lang)
    {
        if (empty($this->instructions[$lang])) {
            $this->throwValidationError("instructions {$lang} is required");
        }
    }

    protected function validateLanguageConsistency()
    {
        if (count($this->instructions['ar']) !== count($this->instructions['en'])) {
            $this->throwValidationError('instructions ar and en should be the same count');
        }
    }

    protected function throwValidationError(string $message)
    {
        throw new HttpResponseException(json(null, $message, 'fail', 422));
    }
}
