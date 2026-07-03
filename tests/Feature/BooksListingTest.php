<?php

use App\Models\Book;
use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

it('renders the books listing with availability for the frontend', function (): void {
    $user = User::factory()->create();

    actingAs($user);

    $this->withoutVite();

    $response = get(route('books.index'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page->component('Books/Index'));
});

it('displays seeded books in the listing', function (): void {
    $user = User::factory()->create();

    Book::factory()->count(3)->create([
        'title' => 'Test Book Title',
    ]);

    actingAs($user);

    $this->withoutVite();

    $response = get('/api/books');

    $response->assertOk();
    $response->assertJsonStructure([
        'data' => [
            '*' => [
                'id',
                'title',
                'author_id',
                'isbn_code',
                'total_quantity',
                'borrowed_quantity',
                'available_quantity',
                'active',
            ],
        ],
    ]);
});

it('requires authentication to access books listing', function (): void {
    get(route('books.index'))->assertRedirect('/login');
});
