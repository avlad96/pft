<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CommentController;
use App\Http\Controllers\Api\PostController;
use Illuminate\Support\Facades\Route;

Route::post('login', [AuthController::class, 'login']);
Route::post('register', [AuthController::class, 'register']);

Route::apiResource('posts', PostController::class)->only(['index', 'show']);
Route::get('/posts/{post}/comments', [CommentController::class, 'index']);

Route::group(['middleware' => 'auth:sanctum'], function () {
    Route::apiResource('posts', PostController::class)->only(['store', 'update', 'destroy']);
    Route::post('/posts/{post}/comments', [CommentController::class, 'store']);
    Route::post('/posts/{post}/comments/{comment}/replies', [CommentController::class, 'storeReply']);
    Route::apiResource('comments', CommentController::class)->only(['show', 'update', 'destroy']);
});
