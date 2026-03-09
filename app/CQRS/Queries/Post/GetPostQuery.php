<?php

namespace App\CQRS\Queries\Post;
class GetPostQuery implements \App\CQRS\Contracts\QueryInterface
{
    public function __construct(
        public int $limit,
        public ?int $postId,
    ) 
    {}
}