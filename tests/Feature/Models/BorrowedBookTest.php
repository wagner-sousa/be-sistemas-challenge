<?php


use App\Models\BorrowedBook;
use App\Models\Book;
use App\Models\User;
use Illuminate\Support\Carbon;

it('can create a BorrowedBook with the factory', function () {
    $borrowedBook = BorrowedBook::factory()->create();
    expect($borrowedBook)->toBeInstanceOf(BorrowedBook::class)
        ->book_id->not->toBeNull()
        ->user_id->not->toBeNull()
        ->identifier->not->toBeNull()
        ->started_at->not->toBeNull();
})->group('models');

it('has a relationship with Book', function () {
    $borrowedBook = BorrowedBook::factory()->create();
    expect($borrowedBook)
        ->book->toBeInstanceOf(Book::class)
        ->book->id->toBe($borrowedBook->book_id);
})->group('models');

it('has a relationship with User', function () {
    $borrowedBook = BorrowedBook::factory()->create();

    expect($borrowedBook)
        ->user->toBeInstanceOf(User::class)
        ->user->id->toBe($borrowedBook->user_id);
})->group('models');

it('casts started_at and ended_at to datetime', function () {
    $borrowedBook = BorrowedBook::factory()->create([
        'started_at' => now(),
        'ended_at' => now()->addDays(7),
    ]);

    expect($borrowedBook)
        ->started_at->toBeInstanceOf(Carbon::class)
        ->ended_at->toBeInstanceOf(Carbon::class);

})->group('models');
