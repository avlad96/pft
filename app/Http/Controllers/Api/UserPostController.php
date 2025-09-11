<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PostResource;
use App\Models\User;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Knuckles\Scribe\Attributes\Group;

class UserPostController extends Controller
{
    #[Group("Users")]
    public function index(User $user): ResourceCollection
    {
        return PostResource::collection(
            $user->posts()->with('user')->get()
        );
    }

    #[Group("Users")]
    public function active(User $user): ResourceCollection
    {
        return PostResource::collection(
            $user->posts()->active()->with('user')->get()
        );
    }
}
