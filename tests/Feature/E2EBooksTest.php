<?php

use App\Models\Book;
use App\Models\BorrowedBook;
use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;
use function Pest\Laravel\post;

it('displays books list with correct data structure', function (): void {
    $user = User::factory()->create();
    $book = Book::factory()->create([
        'title' => 'Test Book E2E',
        'total_quantity' => 5,
        'borrowed_quantity' => 2,
    ]);

    actingAs($user);
    $this->withoutVite();

    $response = get('/api/books');

    $response->assertOk();
    $response->assertJsonFragment(['title' => 'Test Book E2E']);
    $response->assertJsonFragment(['available_quantity' => 3]);
});

it('allows user to borrow a book', function (): void {
    $user = User::factory()->create();
    $book = Book::factory()->create([
        'total_quantity' => 5,
        'borrowed_quantity' => 0,
    ]);

    actingAs($user);
    $this->withoutVite();

    $response = post('/api/borrowed-books', [
        'books' => [$book->id],
    ]);

    $response->assertCreated();
    $response->assertJsonStructure(['identifier']);

    // Check that a loan was created
    $loan = BorrowedBook::where('user_id', $user->id)->where('book_id', $book->id)->first();
    expect($loan)->not->toBeNull();
});

it('prevents borrowing a book with no available copies', function (): void {
    $user = User::factory()->create();
    $book = Book::factory()->create([
        'total_quantity' => 1,
        'borrowed_quantity' => 1,
    ]);

    actingAs($user);
    $this->withoutVite();

    $response = post('/api/borrowed-books', [
        'books' => [$book->id],
    ]);

    $response->assertStatus(302); // Redirects with error
});

it('handles pagination correctly', function (): void {
    $user = User::factory()->create();
    Book::factory()->count(20)->create();

    actingAs($user);
    $this->withoutVite();

    $response = get('/api/books?page=1');
    $response->assertOk();
    $response->assertJsonStructure([
        'data',
        'current_page',
        'last_page',
        'per_page',
        'total',
    ]);

    $response = get('/api/books?page=2');
    $response->assertOk();
});
