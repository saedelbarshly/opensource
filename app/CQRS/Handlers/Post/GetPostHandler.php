<?php
namespace App\CQRS\Handlers\Post;

use App\Models\Post;
use Illuminate\Support\Collection;

class GetPostHandler  implements \App\CQRS\Contracts\HandlerInterface
{
    public function handle($message): Collection
    {
        $posts = Post::query()
            ->when($message->postId, function ($q) use ($message) {
                $q->where('id', $message->postId);
            })
            ->orderByDesc('id')
            ->limit($message->limit)
            ->get();

        return $posts;
    }
}