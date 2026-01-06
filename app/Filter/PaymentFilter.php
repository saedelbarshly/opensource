<?php

namespace App\Filter;

use Illuminate\Support\Carbon;

class PaymentFilter extends Filter
{
    protected $var_filters = ['keyword','service','status','period','from','to'];

    public function keyword($keyword)
    {
        $keyword = trim((string) $keyword);

        if ($keyword === '') {
            return $this->builder;
        }

        return $this->builder->where(function ($q) use ($keyword) {
            $q->whereRelation('user', 'name', 'LIKE', "%{$keyword}%")
                ->orWhereRelation('user', 'email', 'LIKE', "%{$keyword}%")
                ->orWhereRelation('payable', 'number', 'LIKE', "%{$keyword}%");
        });
    }


    public function service($service)
    {
        return $this->builder->whereRelation('payable', 'service_id', $service);
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
