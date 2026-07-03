<?php

use App\Models\Author;
use App\Models\Book;
use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\deleteJson;
use function Pest\Laravel\getJson;
use function Pest\Laravel\postJson;
use function Pest\Laravel\putJson;

it('lists authors', function (): void {
    $user = User::factory()->create();
    Author::factory()->count(2)->create();

    actingAs($user, 'sanctum');

    $response = getJson('/api/authors');

    $response->assertOk()
        ->assertJsonStructure([
            'data' => [
                [
                    'id',
                    'name',
                ],
            ],
        ]);
});

it('creates an author', function (): void {
    $user = User::factory()->create();

    actingAs($user, 'sanctum');

    $payload = ['name' => 'New Author'];

    $response = postJson('/api/authors', $payload);

    $response->assertCreated()
        ->assertJsonPath('name', $payload['name']);

    $this->assertDatabaseHas('authors', $payload);
});

it('updates an author', function (): void {
    $user = User::factory()->create();
    $author = Author::factory()->create(['name' => 'Old Name']);

    actingAs($user, 'sanctum');

    $response = putJson("/api/authors/{$author->id}", ['name' => 'Updated Name']);

    $response->assertOk()
        ->assertJsonPath('name', 'Updated Name');

    $this->assertDatabaseHas('authors', ['id' => $author->id, 'name' => 'Updated Name']);
});

it('prevents deleting an author that has books', function (): void {
    $user = User::factory()->create();
    $author = Author::factory()->create();
    Book::factory()->for($author)->create();

    actingAs($user, 'sanctum');

    $response = deleteJson("/api/authors/{$author->id}");

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['author']);

    $this->assertDatabaseHas('authors', ['id' => $author->id]);
});

it('deletes an author without books', function (): void {
    $user = User::factory()->create();
    $author = Author::factory()->create();

    actingAs($user, 'sanctum');

    $response = deleteJson("/api/authors/{$author->id}");

    $response->assertNoContent();
    $this->assertDatabaseMissing('authors', ['id' => $author->id]);
});
