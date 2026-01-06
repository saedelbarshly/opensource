<?php

namespace App\Filter;

use Illuminate\Support\Carbon;

class WithdrawRequestFilter extends Filter
{
    protected $var_filters = ['keyword','type','status','period','from','to'];

    public function keyword($keyword)
    {
        return $this->builder->whereRaw("RIGHT(number, 6) = ?", [$keyword]);
    }

    public function type($type)
    {
        return $this->builder->where('type', $type);
    }

    public function status($status)
    {
        return $this->builder->where('status', $status);
    }

    public function period($period)
    {
        $date = getDateRangeFilter($period);
        return $this->builder->whereBetween('created_at', [$date['from'], $date['to']]);
    }

    public function from($from)
    {
        return $this->builder->where('created_at', '>=', Carbon::parse($from)->startOfDay());
    }

    public function to($to)
    {
        return $this->builder->where('created_at', '<=', Carbon::parse($to)->endOfDay());
    }


}
