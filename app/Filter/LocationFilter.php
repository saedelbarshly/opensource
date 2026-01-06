<?php

namespace App\Filter;

class LocationFilter extends Filter
{
    protected $var_filters = ['keyword'];

    public function keyword($keyword)
    {
        $this->builder->where('name', 'like', "%$keyword%");
    }
}
