<?php
namespace App\CQRS\Contracts;


interface HandlerInterface {
    public function handle(CommandInterface|QueryInterface $message): mixed;
}