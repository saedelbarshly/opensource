<?php
namespace App\Enums;

enum UserType: string
{
    case ADMIN   = 'admin';
    case VENDOR  = 'vendor';
    case CLIENT  = 'client';
    case GUEST   = 'guest';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function getLabel(): string
    {
        return trans('translation.enums.' . $this->value);
    }

     public static function authType(string $userType): string
    {
        return match ($userType) {
            self::ADMIN->value    => 'email',
            self::VENDOR->value   => 'email',
            self::CLIENT->value   => 'phone',
            default               => 'phone',
        };
    }
}

