<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $guarded = ['id', 'created_at', 'updated_at'];

    public function setValueAttribute($value)
    {
        $key = $this->attributes['key'] ?? null;

        switch ($key) {
            case 'instructions':
                $this->attributes['value'] = json_encode($value);
                break;

            //            case 'locations':
            //                if (is_array($value)) {
            //                    $this->attributes['value'] = json_encode([
            //                        'lat' => $value['lat'] ?? null,
            //                        'lng' => $value['lng'] ?? null,
            //                        'address' => $value['address'] ?? '',
            //                    ]);
            //                }
            //                break;

            case 'client_video':
            case 'vendor_video':
                if (!is_null($value)) {
                    $this->attributes['value'] = asset('storage/files/settings/' . $value);
                } else {
                    $this->attributes['value'] = null;
                }
                break;

            default:
                $this->attributes['value'] = $value;
        }
    }


    public function getValueAttribute()
    {
        if (count($this->attributes) > 0) {
            if ($this->attributes['key'] == 'locations') return json_decode($this->attributes['value']);
            return $this->attributes['value'];
        }
    }
}
