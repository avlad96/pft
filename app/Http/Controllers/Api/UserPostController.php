<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PostResource;
use App\Models\User;
use Illuminate\Http\Resources\Json\ResourceCollection;

class UserPostController extends Controller
{
    public function index(User $user): ResourceCollection
    {
        return PostResource::collection(
            $user->posts()->with('user')->get()
        );
    }

    public function active(User $user): ResourceCollection
    {
        return PostResource::collection(
            $user->posts()->active()->with('user')->get()
        );
    }
}
