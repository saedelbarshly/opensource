<?php

namespace Modules\Media\Http\Requests;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\ApiMasterRequest;

class StoreRequest extends ApiMasterRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $tables = DB::select('SHOW TABLES');
        $res = array_map(fn($table) => current((array) $table), $tables);
        
        return [
            'file'                  => ['required', 'file'],
            'media_type'            => ['required', Rule::in(['image', 'video', 'audio' ,'file'])],
            'model'                 => ['required', Rule::in($res)],
            'deleted_media'         => ['sometimes', 'string', Rule::exists('media', 'id')],
            'width'                 => ['sometimes', 'numeric'],
            'height'                => ['sometimes', 'numeric'],
            'quality'               => ['sometimes', 'numeric', 'min:1', 'max:100'],
            'generate_thumbnail'    => ['sometimes', 'boolean'],
            'generate_slh'          => ['sometimes', 'boolean'],
        ];
    }
}
