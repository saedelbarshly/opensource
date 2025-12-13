<?php

namespace App\Http\Requests\Api\General\Media;


use App\Http\Requests\ApiMasterRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

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
            'file'            => 'required',
            'media_type'      => ['required', Rule::in(['image', 'video', 'audio' ,'file'])],
            'model'           => ['required', Rule::in($res)],
            'deleted_media'   => ['sometimes', 'string', Rule::exists('media', 'id')],
        ];
    }
}
