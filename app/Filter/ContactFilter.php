<?php

namespace App\Filter;

class ContactFilter extends Filter
{
    protected $var_filters = ['name', 'email', 'read', 'status'];

    public function name($name)
    {
        return $this->builder->where('name', 'like', "{$name}%");
    }

    public function email($email)
    {
        return $this->builder->where('email', 'like', "{$email}%");
    }

    public function read($read)
    {
        $read = filter_var($read, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

        return match ($read) {
            true  => $this->builder->whereNotNull('read_at'),
            false => $this->builder->whereNull('read_at'),
            default => $this->builder,
        };
    }
}
