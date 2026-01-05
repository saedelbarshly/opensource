<?php

namespace App\Services\General;

use Illuminate\Database\Eloquent\Model;

abstract class Sluggable extends Model
{
    public function setAttribute($key, $value)
    {

        if (! $this->hasStringKeys($this->sluggable()) || in_array($key, $this->sluggable())) {
            return $this->slugger($key, $value);
        }
        return parent::setAttribute($key, $value);
    }

    abstract protected function sluggable(): array;

    private function slugger($key, $value): static
    {
        if ($this->locale == 'ar') {
            $slug = trim(
                mb_strtolower(preg_replace('/[^\p{L}\p{N}\x{0600}-\x{06FF}]+/u', '-', $value)),
                '-'
            );
        } else {
            $slug = str($value)
                ->slug(language: $this->locale ?? app()->getLocale())
                ->lower()
                ->value();
        }

        $this->attributes[$key] = $this->generateUniqueSlug($slug, $key);
        return $this;
    }

    private function generateUniqueSlug($slug, $key): string
    {
        $baseSlug = $slug;
        $count = 1;

        while (static::where($key, $slug)->where('id', '!=', $this->id)->exists()) {
            $slug = $baseSlug . '-' . $count;
            $count++;
        }

        return $slug;
    }

    private function hasStringKeys(array $array): bool
    {
        return count(array_filter(array_keys($array), 'is_string')) > 0;
    }
}