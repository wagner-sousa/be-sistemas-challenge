<?php

namespace App\Data;

use App\Models\BorrowedBook;
use Carbon\Carbon;
use Spatie\LaravelData\Data;

class BorrowedBookData extends Data
{
    public function __construct(
        public int $id,
        public int $book_id,
        public int $user_id,
        public string $identifier,
        public ?Carbon $started_at,
        public ?Carbon $ended_at,
        public ?Carbon $predicted_end_at,
        public bool $is_overdue,
        public ?array $book,
        public ?array $user,
    ) {}

    public static function fromModel(BorrowedBook $borrowedBook): self
    {
        return new self(
            id: $borrowedBook->id,
            book_id: $borrowedBook->book_id,
            user_id: $borrowedBook->user_id,
            identifier: $borrowedBook->identifier,
            started_at: $borrowedBook->started_at,
            ended_at: $borrowedBook->ended_at,
            predicted_end_at: $borrowedBook->predicted_end_at,
            is_overdue: $borrowedBook->is_overdue,
            book: $borrowedBook->relationLoaded('book') ? [
                'id' => $borrowedBook->book?->id,
                'title' => $borrowedBook->book?->title,
                'author' => $borrowedBook->book?->author?->name,
                'isbn_code' => $borrowedBook->book?->isbn_code,
                'total_quantity' => $borrowedBook->book?->total_quantity,
                'borrowed_quantity' => $borrowedBook->book?->borrowed_quantity,
                'available_quantity' => $borrowedBook->book?->available_quantity,
            ] : null,
            user: $borrowedBook->relationLoaded('user') ? [
                'id' => $borrowedBook->user?->id,
                'name' => $borrowedBook->user?->name,
                'email' => $borrowedBook->user?->email,
            ] : null,
        );
    }
}
