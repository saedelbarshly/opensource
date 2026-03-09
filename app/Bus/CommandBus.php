<?php

namespace App\Bus;

use App\CQRS\Contracts\CommandInterface;
use App\CQRS\Contracts\HandlerInterface;
use Illuminate\Contracts\Container\Container;

class CommandBus
{
    public function __construct(private Container $container) {}

    public function dispatch(CommandInterface $command): mixed
    {
        $handler = $this->resolveHandler($command);
        return $handler->handle($command);
    }

    private function resolveHandler(CommandInterface $command): HandlerInterface
    {
        // Convention: CreateUserCommand → CreateUserHandler
        $handlerClass = str_replace('Command', 'Handler', get_class($command));
        return $this->container->make($handlerClass);
    }
}
