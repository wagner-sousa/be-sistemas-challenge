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
    Route::apiResource('authors', AuthorController::class)->names([
        'index' => 'api.authors.index',
        'store' => 'api.authors.store',
        'show' => 'api.authors.show',
        'update' => 'api.authors.update',
        'destroy' => 'api.authors.destroy',
    ]);
    Route::apiResource('books', ApiBookController::class)->names([
        'index' => 'api.books.index',
        'store' => 'api.books.store',
        'show' => 'api.books.show',
        'update' => 'api.books.update',
        'destroy' => 'api.books.destroy',
    ]);

    Route::post('borrowed-books/return/{identifier}', [BorrowedBookController::class, 'returnBooksByIdentifier'])
        ->middleware('throttle:borrow')
        ->name('api.borrowed-books.return-by-identifier');

    Route::patch('borrowed-books/return/book/{borrowedBook}', [BorrowedBookController::class, 'returnBorrowedBook'])
        ->middleware('throttle:borrow')
        ->name('api.borrowed-books.return');

    Route::apiResource('borrowed-books', BorrowedBookController::class)
        ->only(['index', 'store', 'show'])
        ->names([
            'index' => 'api.borrowed-books.index',
            'store' => 'api.borrowed-books.store',
            'show' => 'api.borrowed-books.show',
        ])
        ->middleware('throttle:borrow');
});
