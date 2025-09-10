<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StorePostRequest;
use App\Http\Resources\PostResource;
use App\Models\Post;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class PostController extends Controller
{
    public function index(): ResourceCollection
    {
        return PostResource::collection(Post::active()->with('user')->get());
    }

    public function store(StorePostRequest $request): PostResource
    {
        return new PostResource(
            Auth::user()->posts()->create($request->validated())
        );
    }

    public function show(Post $post): PostResource
    {
        $post->load('user');

        return new PostResource($post);
    }

    public function update(StorePostRequest $request, Post $post): PostResource
    {
        Gate::authorize('update', $post);

        $post->update($request->validated());

        return new PostResource($post);
    }

    public function destroy(Post $post): Response
    {
        Gate::authorize('delete', $post);

        $post->delete();

        return response()->noContent();
    }
}
