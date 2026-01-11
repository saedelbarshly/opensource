<?php
namespace App\Enums;

enum CouponType: string
{
    case PERCENTAGE = 'percentage';
    case FIXED      = 'fixed';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

     public function getLabel(): string
    {
        return trans('translation.enums.' . $this->value);
    }

}

