<?php

use App\Models\Book;
use App\Models\Author;

it('can create a book with the factory', function () {
    $book = Book::factory()->create();
    expect($book)->toBeInstanceOf(Book::class)
        ->and($book->exists)->toBeTrue();
})->group('models');

it('can access the author of a book', function () {
    $book = Book::factory()->create();
    $author = $book->author;
    expect($author)->toBeInstanceOf(Author::class)
        ->and($author->id)->toBe($book->author_id);
})->group('models');
