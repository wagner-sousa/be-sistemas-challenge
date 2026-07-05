<?php

use App\Models\Book;
use App\Models\BorrowedBook;
use App\Models\User;
use App\Services\BorrowedBookService;

use function Pest\Laravel\actingAs;

function makeServiceWithBook(Book $book): BorrowedBookService
{
    $service = app(BorrowedBookService::class);
    $service->addBook($book->id);

    return $service;
}

it('prevents concurrent borrowing of the same book by different users', function (): void {
    $book = Book::factory()->create([
        'total_quantity' => 1,
        'borrowed_quantity' => 0,
    ]);

    /** @var User $firstUser */
    $firstUser = User::factory()->create();
    /** @var User $secondUser */
    $secondUser = User::factory()->create();

    actingAs($firstUser);
    $firstService = makeServiceWithBook($book);
    $firstService->commitBorrowBooks();

    expect($book->refresh()->borrowed_quantity)->toBe(1);
    expect($firstUser->refresh()->current_borrowed_books)->toBe(1);

    actingAs($secondUser);
    $secondService = app(BorrowedBookService::class);

    expect(function () use ($secondService, $book): void {
        $secondService->addBook($book->id);
        $secondService->commitBorrowBooks();
    })->toThrow(Exception::class, 'Quantidade de livros disponíveis insuficiente!');

    expect(BorrowedBook::query()->count())->toBe(1);
    expect($book->refresh()->available_quantity)->toBe(0);
});

it('blocks borrowing when user already reached the configured limit', function (): void {
    /** @var User $user */
    $user = User::factory()->create([
        'current_borrowed_books' => 3,
    ]);

    $book = Book::factory()->create([
        'total_quantity' => 2,
        'borrowed_quantity' => 0,
    ]);

    actingAs($user);

    $service = makeServiceWithBook($book);

    expect(fn () => $service->commitBorrowBooks())
        ->toThrow(Exception::class, 'Você já possui 3 livros emprestados. O limite é de 3 empréstimos ativos.');

    expect(BorrowedBook::query()->count())->toBe(0);
});
