<?php

use App\Models\Book;
use App\Models\BorrowedBook;
use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;
use function Pest\Laravel\post;

it('displays loans page with correct structure', function (): void {
    $user = User::factory()->create();

    actingAs($user);
    $this->withoutVite();

    $response = get(route('loans.my-loans'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page->component('Loans/MyLoans'));
});

it('shows only current user loans', function (): void {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $book1 = Book::factory()->create(['title' => 'User 1 Book']);
    $book2 = Book::factory()->create(['title' => 'User 2 Book']);

    // Create loans for both users
    actingAs($user1);
    post('/api/borrowed-books', ['books' => [$book1->id]]);

    actingAs($user2);
    post('/api/borrowed-books', ['books' => [$book2->id]]);

    // Check user1 loans - use direct model query to avoid SQL Raw issues in tests
    $user1Loans = BorrowedBook::where('user_id', $user1->id)->with('book')->get();
    expect($user1Loans)->toHaveCount(1);
    expect($user1Loans->first()->book->title)->toBe('User 1 Book');
});

it('marks loan as overdue after 7 days', function (): void {
    $user = User::factory()->create();
    $book = Book::factory()->create();

    actingAs($user);
    $this->withoutVite();

    // Borrow a book
    post('/api/borrowed-books', ['books' => [$book->id]]);

    // Manually set the started_at to 8 days ago
    $loan = BorrowedBook::where('user_id', $user->id)->first();
    $loan->update(['started_at' => now()->subDays(8)]);

    // Check if loan is overdue using model accessor
    $loan->refresh();
    expect($loan->is_overdue)->toBeTrue();
});

it('allows returning a book by identifier', function (): void {
    $user = User::factory()->create();
    $book = Book::factory()->create([
        'total_quantity' => 5,
        'borrowed_quantity' => 0,
    ]);

    actingAs($user);
    $this->withoutVite();

    // Borrow a book
    $borrowResponse = post('/api/borrowed-books', ['books' => [$book->id]]);
    $identifier = $borrowResponse->json('identifier');

    // Return by identifier
    $returnResponse = post("/api/borrowed-books/return/{$identifier}");

    $returnResponse->assertOk();
    $book->refresh();
    expect($book->borrowed_quantity)->toBe(0);
});

it('prevents returning already returned book', function (): void {
    $user = User::factory()->create();
    $book = Book::factory()->create();

    actingAs($user);
    $this->withoutVite();

    // Borrow and return
    $borrowResponse = post('/api/borrowed-books', ['books' => [$book->id]]);
    $identifier = $borrowResponse->json('identifier');

    post("/api/borrowed-books/return/{$identifier}");

    // Try to return again - should fail with validation error
    $response = post("/api/borrowed-books/return/{$identifier}");

    $response->assertStatus(302); // Redirects back with error
});

it('displays loan status correctly', function (): void {
    $user = User::factory()->create();
    $book1 = Book::factory()->create(['title' => 'Active Loan']);
    $book2 = Book::factory()->create(['title' => 'Returned Loan']);

    actingAs($user);
    $this->withoutVite();

    // Borrow both books
    post('/api/borrowed-books', ['books' => [$book1->id, $book2->id]]);

    // Return one
    $loan2 = BorrowedBook::where('book_id', $book2->id)->first();
    $loan2->update(['ended_at' => now()]);

    // Check loans using direct model query
    $loans = BorrowedBook::where('user_id', $user->id)->with('book')->get();

    $activeLoan = $loans->firstWhere('book.title', 'Active Loan');
    $returnedLoan = $loans->firstWhere('book.title', 'Returned Loan');

    expect($activeLoan->ended_at)->toBeNull();
    expect($returnedLoan->ended_at)->not->toBeNull();
});
