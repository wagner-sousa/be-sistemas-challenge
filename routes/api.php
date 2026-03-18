<?php

use App\Http\Controllers\Api\AuthorController;
use App\Http\Controllers\Api\BookController as ApiBookController;
use App\Http\Controllers\Api\BorrowedBookController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware(['auth:sanctum'])->group(function (): void {
    Route::apiResource('authors', AuthorController::class);
    Route::apiResource('books', ApiBookController::class);

    Route::post('borrowed-books/return/{identifier}', [BorrowedBookController::class, 'returnBooksByIdentifier'])
        ->name('borrowed-books.return-by-identifier');

    Route::patch('borrowed-books/return/book/{borrowedBook}', [BorrowedBookController::class, 'returnBorrowedBook'])
        ->name('borrowed-books.return');

    Route::apiResource('borrowed-books', BorrowedBookController::class)
        ->only(['index', 'store', 'show']);
});
