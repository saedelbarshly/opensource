<?php

namespace App\Filter;

use Carbon\Carbon;

class CouponFilter extends Filter
{
    protected $var_filters = ['keyword','type', 'category', 'status','duration', 'from', 'to'];

    public function keyword($keyword)
    {
        return $this->builder->where('name', 'like', "%$keyword%");
    }
    public function type($type)
    {
        return $this->builder->where('type', $type);
    }

    public function category($category)
    {
        return $this->builder->where('category', $category);
    }

    public function status($status)
    {
        return match ($status) {
            'active' => $this->builder
                ->where('start_at', '<=', now())
                ->where('end_at', '>=', now())
                ->where('is_active', true),

            'upcoming' => $this->builder
                ->where('start_at', '>', now())
                ->where('is_active', true),

            'expired' => $this->builder
                ->where('end_at', '<', now()),

            'suspended' => $this->builder
                ->where('is_active', false),

            default => $this->builder,
        };
    }

    public function duration($duration)
    {
        $range = getDateRangeFilter($duration);
        return $this->builder
            ->whereDate('start_at', '>=', $range['from'])
            ->whereDate('end_at', '<=', $range['to']);
    }

    public function from($from)
    {
        return $this->builder->where('start_at', '>=', Carbon::parse($from)->startOfDay());
    }

    public function to($to)
    {
        return $this->builder->where('end_at', '<=', Carbon::parse($to)->endOfDay());
    }


}
