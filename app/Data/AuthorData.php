<?php

namespace App\Data;

use App\Models\Author;
use Spatie\LaravelData\Data;

class AuthorData extends Data
{
    public function __construct(
        public int $id,
        public string $name,
    ) {}

    public static function fromModel(Author $author): self
    {
        return new self(
            id: $author->id,
            name: $author->name,
        );
    }
}
