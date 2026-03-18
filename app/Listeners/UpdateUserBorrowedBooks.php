<?php

namespace App\Listeners;

use App\Events\BorrowedBookCreated;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;

class UpdateUserBorrowedBooks implements ShouldDispatchAfterCommit
{
    /**
     * Handle the event.
     */
    public function handle(BorrowedBookCreated $event): void
    {
        $borrowedBook = $event->getBorrowedBook();
        $user = $event->getUser();

        if ($borrowedBook->ended_at) {
            $user->decrement('current_borrowed_books');
        } else {
            $user->increment('current_borrowed_books');
        }
    }
}
