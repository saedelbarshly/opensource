<?php

namespace App\Filter;

use App\Enums\OrderStatus;

class OrderFilter extends Filter
{
    protected $var_filters = [
        'keyword','service',
        'service_type','status',
        'period','client',
        'from','to'
    ];

    public function service($service)
    {
        return $this->builder->where('service_id', $service);
    }

    public function client($client)
    {
        return $this->builder->where('user_id', $client);
    }

    public function service_type($service_type)
    {
        return $this->builder->whereRelation('service', function ($query) use ($service_type) {
            $query->where('type', $service_type);
        });
    }

    public function status($status)
    {
        $groups = [
            'active' => [
                OrderStatus::PENDING,
                OrderStatus::OFFER_PRICE_PENDING,
                OrderStatus::ASSIGNED,
                OrderStatus::ON_THE_WAY,
                OrderStatus::ARRIVED,
                OrderStatus::IN_PROGRESS,
            ],
            'cancel' => [
                OrderStatus::CANCELLED,
                OrderStatus::AUTO_CANCEL,
                OrderStatus::CANCEL_BEFORE_START,
                OrderStatus::CANCEL_BEFORE_ARRIVED,
                OrderStatus::CANCEL_AFTER_ARRIVED,
            ],

            'completed' => [
                OrderStatus::COMPLETED,
                OrderStatus::CONFIRMED,
            ],
        ];

        return $this->builder->when(
            isset($groups[$status]),
            fn($q) => $q->whereIn('status', $groups[$status]),
            fn($q) => $q->when($status, fn($q) => $q->where('status', $status))
        );
    }

    public function period($period)
    {
        $date = getDateRangeFilter($period);
        return $this->builder->whereBetween('date_from', [$date['from'], $date['to']]);
    }

    public function from($from)
    {
        return $this->builder->where('date_from', '>=', $from.' 00:00:00');
    }

    public function to($to)
    {
        return $this->builder->where('date_to', '<=', $to.' 23:59:59');
    }

    public function keyword($keyword)
    {
        return $this->builder->where(function ($q) use ($keyword) {
            $q->where('number', 'like', "%{$keyword}%")
//                ->orWhereRelation('service', 'name', 'like', "%{$keyword}%")
                ->orWhereRelation('fromLocation', 'name', 'like', "%{$keyword}%")
                ->orWhereRelation('toLocation', 'name', 'like', "%{$keyword}%")
                ->orWhereRelation('client', 'name', 'like', "%{$keyword}%");
        });
    }


}
