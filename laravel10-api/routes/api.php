<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
// use App\Http\Controllers\Api\PostController;
use Illuminate\Session\Middleware\StartSession;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\BookController;
use App\Http\Controllers\Api\MemberController;
use App\Http\Controllers\Api\LoanController;

// Route::apiResource('/posts', PostController::class);
Route::middleware([StartSession::class])->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::apiResource('/users', UserController::class);
    Route::get('/users/{id}', [UserController::class, 'show']);
    Route::post('/categories/import', [CategoryController::class, 'importExcel']);
    Route::get('/categories/export', [CategoryController::class, 'exportExcel']);
    Route::apiResource('/categories', CategoryController::class);
    Route::get('/categories/{id}', [CategoryController::class, 'show']);
    Route::get('/categories/{id}/audits', [CategoryController::class, 'audits']);
    Route::get('/books/export', [BookController::class, 'exportExcel']);
    Route::post('/books/import', [BookController::class, 'importExcel']);
    Route::apiResource('/books', BookController::class);
    Route::get('/books/{id}', [BookController::class, 'show']);
    Route::get('/books/{id}/audits', [BookController::class, 'audits']);
    Route::get('/members/export', [MemberController::class, 'exportExcel']);
    Route::post('/members/import', [MemberController::class, 'importExcel']);
    Route::apiResource('/members', MemberController::class);
    Route::get('/members/{id}', [MemberController::class, 'show']);
    Route::get('/members/{id}/audits', [MemberController::class, 'audits']);
    Route::get('/loans/export', [LoanController::class, 'exportExcel']);
    Route::post('/loans/import', [LoanController::class, 'importExcel']);
    Route::apiResource('/loans', LoanController::class);
    Route::get('/loans/{id}', [LoanController::class, 'show']);
    Route::get('/loans/{id}/audits', [LoanController::class, 'audits']);
});
