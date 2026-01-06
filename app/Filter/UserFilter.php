<?php

namespace App\Filter;

class UserFilter extends Filter
{
    protected $var_filters = [
        'name','keyword',
        'email', 'user_type',
        'phone', 'service_type',
        'service', 'online',
        'company', 'active',
        'order' , 'period'
    ];

    public function name($name)
    {
        $this->builder->where('name', 'like', "%$name%");
    }

    public function keyword($keyword)
    {
        return $this->builder->where(function ($query) use ($keyword) {
            $query->whereAny(['name', 'email'], 'like', "$keyword%")
                ->orWhereRelation('orders', 'number', 'like', "$keyword%");
        });
    }

    public function active($active)
    {
        return $this->builder->where('is_active', $active);
    }

    public function period($period)
    {

    }

    public function email($email)
    {
        return $this->builder->where('email', $email);
    }

    public function user_type($user_type)
    {
        return $this->builder->where('user_type', $user_type);
    }

    public function company($company)
    {
        return $this->builder->where('company_id', $company);
    }

    public function online($online)
    {
        return $this->builder->where('online', $online);
    }

    public function service($service)
    {
        return $this->builder->whereHas('classification', function ($q) use ($service) {
            $q->where('driver_service.service_id', $service);
        });
    }
}
