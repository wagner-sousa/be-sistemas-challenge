<?php

namespace App\Listeners;

use App\Contracts\UpdateBorrowedBookQuantity;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;

class UpdateBookBorrowedQuantity implements ShouldDispatchAfterCommit
{
    /**
     * Handle the event.
     */
    public function handle(UpdateBorrowedBookQuantity $event): void
    {
        $borrowedBook = $event->getBorrowedBook();
        $book = $borrowedBook->book;

        if ($borrowedBook->ended_at) {
            $book->decrement('borrowed_quantity');
        } else {
            $book->increment('borrowed_quantity');
        }
    }
}
