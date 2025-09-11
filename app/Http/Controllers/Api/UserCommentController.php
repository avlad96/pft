<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CommentResource;
use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Facades\Auth;
use Knuckles\Scribe\Attributes\Authenticated;
use Knuckles\Scribe\Attributes\Group;

class UserCommentController extends Controller
{
    #[Group("Users")]
    public function index(User $user): ResourceCollection
    {
        return CommentResource::collection(
            $user->comments()
                ->whereHasMorph(
                    'commentable',
                    [Post::class, Comment::class],
                    function (Builder $query, string $type) {
                        if ($type === Post::class) {
                            $query->active();
                        }
                    }
                )
                ->with('commentable')
                ->get()
        );
    }

    #[Group("Users")]
    #[Authenticated]
    public function comments(): ResourceCollection
    {
        return CommentResource::collection(Auth::user()->comments);
    }
}
