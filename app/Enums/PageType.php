<?php
namespace App\Enums;

enum PageType: string
{
    case Privacy = "privacy";

    case About = "about";

    case Terms = "terms";

    case Contact = "contact";
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function getLabel(): string
    {
        return trans('translation.enums.' . $this->value);
    }

}

