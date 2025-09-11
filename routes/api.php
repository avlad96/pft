<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CommentController;
use App\Http\Controllers\Api\PostController;
use App\Http\Controllers\Api\UserCommentController;
use App\Http\Controllers\Api\UserPostController;
use Illuminate\Support\Facades\Route;

Route::post('login', [AuthController::class, 'login']);
Route::post('register', [AuthController::class, 'register']);

Route::apiResource('posts', PostController::class)->only(['index', 'show']);
Route::get('/posts/{post}/comments', [CommentController::class, 'index']);

Route::get('/comments/{comment}', [CommentController::class, 'show']);
Route::get('/comments/{comment}/replies', [CommentController::class, 'getReplies']);

Route::get('/users/{user}/posts', [UserPostController::class, 'index']);
Route::get('/users/{user}/posts/active', [UserPostController::class, 'active']);
Route::get('/users/{user}/comments', [UserCommentController::class, 'index']);

Route::group(['middleware' => 'auth:sanctum'], function () {
    Route::apiResource('posts', PostController::class)->only(['store', 'update', 'destroy']);
    Route::post('/posts/{post}/comments', [CommentController::class, 'store']);

    Route::post('/comments/{comment}/replies', [CommentController::class, 'storeReply']);
    Route::apiResource('comments', CommentController::class)->only(['update', 'destroy']);

    Route::get('/user/comments', [UserCommentController::class, 'comments']);
});
