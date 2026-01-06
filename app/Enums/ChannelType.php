<?php

namespace App\Enums;

enum ChannelType: string
{
    case ALL = 'all';
    case EMAIL = 'email';
    case NOTIFICATION = 'notification';
    case SMS = 'sms';
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function getLabel(): string
    {
        return trans('translation.enums.' . $this->value);
    }
}
