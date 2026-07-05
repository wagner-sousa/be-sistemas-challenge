<?php

use App\Models\Book;
use App\Models\BorrowedBook;
use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\post;

it('prevents duplicate borrowing with idempotency key', function (): void {
    $user = User::factory()->create();
    $book = Book::factory()->create([
        'total_quantity' => 5,
        'borrowed_quantity' => 0,
    ]);

    actingAs($user);
    $this->withoutVite();

    $idempotencyKey = 'test-key-123';

    // First request
    $response1 = post('/api/borrowed-books', [
        'books' => [$book->id],
        'idempotency_key' => $idempotencyKey,
    ]);

    $response1->assertCreated();
    $identifier = $response1->json('identifier');

    // Second request with same key (should return same result)
    $response2 = post('/api/borrowed-books', [
        'books' => [$book->id],
        'idempotency_key' => $idempotencyKey,
    ]);

    $response2->assertOk();
    expect($response2->json('identifier'))->toBe($identifier);
    expect($response2->json('duplicate'))->toBeTrue();

    // Verify only one loan was created
    $loansCount = BorrowedBook::where('user_id', $user->id)->count();
    expect($loansCount)->toBe(1);
});

it('allows different requests without idempotency key', function (): void {
    $user = User::factory()->create();
    $book1 = Book::factory()->create(['total_quantity' => 5, 'borrowed_quantity' => 0]);
    $book2 = Book::factory()->create(['total_quantity' => 5, 'borrowed_quantity' => 0]);

    actingAs($user);
    $this->withoutVite();

    // First request
    $response1 = post('/api/borrowed-books', [
        'books' => [$book1->id],
    ]);

    $response1->assertCreated();

    // Second request (different book, no idempotency key)
    $response2 = post('/api/borrowed-books', [
        'books' => [$book2->id],
    ]);

    $response2->assertCreated();

    // Verify two loans were created
    $loansCount = BorrowedBook::where('user_id', $user->id)->count();
    expect($loansCount)->toBe(2);
});
