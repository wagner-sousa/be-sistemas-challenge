<?php

use App\Models\Book;
use App\Models\BorrowedBook;
use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\getJson;
use function Pest\Laravel\patchJson;
use function Pest\Laravel\postJson;

it('lists borrowed books for the authenticated user', function (): void {
    $user = User::factory()->create();
    $book = Book::factory()->create();

    actingAs($user, 'sanctum');

    BorrowedBook::factory()->for($book)->for($user)->create([
        'identifier' => 'abc',
        'ended_at' => null,
    ]);

    $response = getJson('/api/borrowed-books');

    $response->assertOk()
        ->assertJsonStructure([
            'data' => [
                [
                    'id',
                    'book_id',
                    'user_id',
                    'identifier',
                    'started_at',
                    'ended_at',
                    'predicted_end_at',
                    'is_overdue',
                    'book',
                    'user',
                ],
            ],
        ]);
});

it('creates borrowed books using the service and returns the identifier', function (): void {
    $user = User::factory()->create();
    $books = Book::factory()->count(2)->create();

    actingAs($user, 'sanctum');

    $response = postJson('/api/borrowed-books', [
        'books' => $books->pluck('id')->all(),
    ]);

    $response->assertCreated()
        ->assertJsonStructure(['identifier']);

    $identifier = $response->json('identifier');

    foreach ($books as $book) {
        $this->assertDatabaseHas('borrowed_books', [
            'book_id' => $book->id,
            'identifier' => $identifier,
            'ended_at' => null,
        ]);
    }
});

it('shows a borrowed book with relations', function (): void {
    $user = User::factory()->create();
    $book = Book::factory()->create();

    actingAs($user, 'sanctum');

    $borrowed = BorrowedBook::factory()->for($book)->for($user)->create([
        'identifier' => 'xyz',
        'ended_at' => null,
    ]);

    $response = getJson("/api/borrowed-books/{$borrowed->id}");

    $response->assertOk()
        ->assertJsonPath('id', $borrowed->id)
        ->assertJsonPath('book.id', $book->id);
});

it('returns a borrowed book', function (): void {
    $user = User::factory()->create();
    $book = Book::factory()->create();

    actingAs($user, 'sanctum');

    $borrowed = BorrowedBook::factory()->for($book)->for($user)->create([
        'identifier' => 'ret-1',
        'ended_at' => null,
    ]);

    $response = patchJson("/api/borrowed-books/{$borrowed->id}/return");

    $response->assertOk()
        ->assertJsonPath('ended_at', fn ($value) => $value !== null);

    expect($borrowed->fresh()->ended_at)->not()->toBeNull();
});

it('returns borrowed books by identifier', function (): void {
    $user = User::factory()->create();
    $books = Book::factory()->count(2)->create();

    actingAs($user, 'sanctum');

    $response = postJson('/api/borrowed-books', [
        'books' => $books->pluck('id')->all(),
    ]);

    $identifier = $response->json('identifier');

    $returnResponse = postJson("/api/borrowed-books/identifier/{$identifier}/return");

    $returnResponse->assertOk();

    $books->each(function (Book $book) use ($identifier): void {
        $borrowed = BorrowedBook::query()
            ->where('book_id', $book->id)
            ->where('identifier', $identifier)
            ->first();

        expect($borrowed?->ended_at)->not()->toBeNull();
    });
});
