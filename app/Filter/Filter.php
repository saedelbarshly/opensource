<?php
namespace App\Filter;
use Illuminate\Http\Request;
class Filter
{
    public $request;

    protected $var_filters = [];

    protected $builder;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function apply($builder)
    {
        $this->builder = $builder;
        foreach ($this->getFilterAttr() as $name=>$value)
        {
            if (method_exists($this,$name))
            {
                $this->$name($value);
            }
        }
        return $this->builder;
    }

    public function getFilterAttr(): array
    {
//        return array_filter($this->request->only($this->var_filters));

        $filters = $this->request->get('filters', []);
        return array_filter(
            array_intersect_key($filters, array_flip($this->var_filters))
        );
    }
}
