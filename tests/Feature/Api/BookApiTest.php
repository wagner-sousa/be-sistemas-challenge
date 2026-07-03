<?php

use App\Models\Author;
use App\Models\Book;
use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;
use function Pest\Laravel\deleteJson;
use function Pest\Laravel\getJson;
use function Pest\Laravel\postJson;
use function Pest\Laravel\putJson;

it('lists books for the authenticated user', function (): void {
    /** @var User $user */
    $user = User::factory()->create();
    Book::factory()->count(3)->create();

    actingAs($user, 'sanctum');

    $response = getJson('/api/books');

    $response->assertOk()
        ->assertJsonStructure([
            'data' => [
                [
                    'id',
                    'title',
                    'author_id',
                    'author',
                    'isbn_code',
                    'total_quantity',
                    'borrowed_quantity',
                    'available_quantity',
                    'active',
                ],
            ],
        ]);
});

it('creates a new book with its author', function (): void {
    /** @var User $user */
    $user = User::factory()->create();

    actingAs($user, 'sanctum');

    $payload = [
        'title' => 'Clean Architecture',
        'author_name' => 'Robert C. Martin',
        'isbn_code' => '9780134494166',
        'total_quantity' => 5,
        'active' => false,
    ];

    $response = postJson('/api/books', $payload);

    $response->assertCreated()
        ->assertJsonPath('title', $payload['title'])
        ->assertJsonPath('author', $payload['author_name'])
        ->assertJsonPath('isbn_code', $payload['isbn_code'])
        ->assertJsonPath('available_quantity', $payload['total_quantity'])
        ->assertJsonPath('active', $payload['active']);

    assertDatabaseHas('authors', ['name' => $payload['author_name']]);
    assertDatabaseHas('books', [
        'title' => $payload['title'],
        'isbn_code' => $payload['isbn_code'],
        'total_quantity' => $payload['total_quantity'],
        'borrowed_quantity' => 0,
        'active' => false,
    ]);
});

it('updates a book details and author', function (): void {
    /** @var User $user */
    $user = User::factory()->create();
    $book = Book::factory()->create();

    actingAs($user, 'sanctum');

    $payload = [
        'title' => 'Refactoring',
        'author_name' => 'Martin Fowler',
        'isbn_code' => '9780201485677',
        'total_quantity' => $book->total_quantity + 1,
        'active' => false,
    ];

    $response = putJson("/api/books/{$book->id}", $payload);

    $response->assertOk()
        ->assertJsonPath('title', $payload['title'])
        ->assertJsonPath('author', $payload['author_name'])
        ->assertJsonPath('isbn_code', $payload['isbn_code'])
        ->assertJsonPath('total_quantity', $payload['total_quantity'])
        ->assertJsonPath('active', $payload['active']);

    $authorId = Author::query()->where('name', $payload['author_name'])->first()->id;

    assertDatabaseHas('books', [
        'id' => $book->id,
        'title' => $payload['title'],
        'author_id' => $authorId,
        'isbn_code' => $payload['isbn_code'],
        'total_quantity' => $payload['total_quantity'],
        'active' => false,
    ]);
});

it('does not allow reducing total quantity below borrowed quantity', function (): void {
    /** @var User $user */
    $user = User::factory()->create();
    $book = Book::factory()->create([
        'total_quantity' => 3,
        'borrowed_quantity' => 2,
    ]);

    actingAs($user, 'sanctum');

    $response = putJson("/api/books/{$book->id}", [
        'total_quantity' => 1,
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['total_quantity']);
});

it('deletes a book without active borrows', function (): void {
    /** @var User $user */
    $user = User::factory()->create();
    $book = Book::factory()->create();

    actingAs($user, 'sanctum');

    $response = deleteJson("/api/books/{$book->id}");

    $response->assertNoContent();
    assertDatabaseMissing('books', ['id' => $book->id]);
});

it('prevents deleting a book with active borrows', function (): void {
    /** @var User $user */
    $user = User::factory()->create();
    $book = Book::factory()->create([
        'borrowed_quantity' => 1,
    ]);

    actingAs($user, 'sanctum');

    $response = deleteJson("/api/books/{$book->id}");

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['book']);

    assertDatabaseHas('books', ['id' => $book->id]);
});
