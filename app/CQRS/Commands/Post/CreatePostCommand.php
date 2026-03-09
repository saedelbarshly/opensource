<?php

namespace App\CQRS\Commands\Post;

use App\CQRS\Contracts\CommandInterface;

final class CreatePostCommand implements CommandInterface
{
    public function __construct(
        public readonly string $content
    ) {}
}