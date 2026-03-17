<?php

namespace App\Events;

use App\Contracts\UpdateBorrowedBookQuantity;
use App\Contracts\UpdateUserBorrowedBookQuantity;
use App\Models\BorrowedBook;
use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BorrowedBookCreated implements UpdateBorrowedBookQuantity, UpdateUserBorrowedBookQuantity
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public BorrowedBook $borrowedBook
    ) {}

    public function getBorrowedBook(): BorrowedBook
    {
        return $this->borrowedBook;
    }

    public function getUser(): User
    {
        return $this->borrowedBook->user;
    }
}
