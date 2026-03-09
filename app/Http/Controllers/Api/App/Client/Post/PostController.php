<?php

namespace App\Http\Controllers\Api\App\Client\Post;

use App\Bus\CommandBus;
use App\Bus\QueryBus;
use App\CQRS\Commands\Post\CreatePostCommand;
use App\CQRS\Queries\Post\GetPostQuery;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;


class PostController extends Controller
{
    public function __construct(
        private CommandBus $commandBus,
        private QueryBus   $queryBus,
    ) {}


    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'content' => 'required|string',
        ]);

        $post = $this->commandBus->dispatch(
            new CreatePostCommand(
                content: $request->content,
            )
        );

        return response()->json($post, 201);
    }

    public function index(): JsonResponse
    {
        $posts = $this->queryBus->dispatch(
            new GetPostQuery(
                limit: 10,
                postId: null,
            )
        );
        return response()->json($posts);
    }
}
