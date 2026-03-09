<?php

namespace App\Bus;

use App\CQRS\Contracts\QueryInterface;
use Illuminate\Contracts\Container\Container;


class QueryBus
{
    public function __construct(private Container $container) {}

    public function dispatch(QueryInterface $query): mixed
    {
        $handlerClass = str_replace(['Query', 'Queries'], ['Handler', 'Handlers'], get_class($query));
        return $this->container->make($handlerClass)->handle($query);
    }
}
