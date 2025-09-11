<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreCommentRequest;
use App\Http\Resources\CommentResource;
use App\Models\Comment;
use App\Models\Post;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Knuckles\Scribe\Attributes\Authenticated;
use Knuckles\Scribe\Attributes\Group;

class CommentController extends Controller
{
    #[Group("Comments")]
    public function index(Post $post): ResourceCollection
    {
        $post->load('comments.replies');

        return CommentResource::collection($post->comments);
    }

    #[Group("Comments")]
    #[Authenticated]
    public function store(StoreCommentRequest $request, Post $post): CommentResource
    {
        $data = $request->validated();

        $comment = $post->comments()->create([
            'user_id' => Auth::id(),
            'body' => $data['body'],
        ]);

        return new CommentResource($comment);
    }

    #[Group("Comments")]
    #[Authenticated]
    public function storeReply(StoreCommentRequest $request, Comment $comment): CommentResource
    {
        $data = $request->validated();

        $comment = $comment->replies()->create([
            'user_id' => Auth::id(),
            'body' => $data['body'],
        ]);

        return new CommentResource($comment);
    }

    #[Group("Comments")]
    public function show(Comment $comment): CommentResource
    {
        $comment->load('user');

        return new CommentResource($comment);
    }

    #[Group("Comments")]
    #[Authenticated]
    public function update(StoreCommentRequest $request, Comment $comment): CommentResource
    {
        Gate::authorize('update', $comment);

        $comment->update($request->validated());

        return new CommentResource($comment);
    }

    #[Group("Comments")]
    #[Authenticated]
    public function destroy(Comment $comment): Response
    {
        Gate::authorize('delete', $comment);

        $comment->delete();

        return response()->noContent();
    }

    #[Group("Comments")]
    public function getReplies(Comment $comment): ResourceCollection
    {
        $comment->load('replies', 'replies.user');

        return CommentResource::collection($comment->replies);
    }
}
