<?php

namespace App\CQRS\Handlers\Post;

use App\CQRS\Contracts\HandlerInterface;
use App\Models\Post;

class CreatePostHandler implements HandlerInterface
{
    public function handle($command): Post
    {
        return Post::create([
            'content' => $command->content,
        ]);
    }
}