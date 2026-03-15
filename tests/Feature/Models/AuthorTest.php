<?php

use App\Models\Author;

it('can create an author with the factory', function () {
    $author = Author::factory()->create();
    expect($author)->toBeInstanceOf(Author::class)
        ->and($author->exists)->toBeTrue();
})->group('models');
