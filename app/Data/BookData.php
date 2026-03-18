<?php

namespace App\Data;

use App\Models\Book;
use Spatie\LaravelData\Data;

class BookData extends Data
{
    public function __construct(
        public int $id,
        public string $title,
        public int $author_id,
        public string $author,
        public string $isbn_code,
        public int $total_quantity,
        public int $borrowed_quantity,
        public int $available_quantity,
        public bool $active,
    ) {}

    public static function fromModel(Book $book): self
    {
        return new self(
            id: $book->id,
            title: $book->title,
            author_id: $book->author_id,
            author: $book->author?->name ?? '',
            isbn_code: $book->isbn_code,
            total_quantity: $book->total_quantity,
            borrowed_quantity: $book->borrowed_quantity,
            available_quantity: $book->available_quantity,
            active: $book->active,
        );
    }
}
