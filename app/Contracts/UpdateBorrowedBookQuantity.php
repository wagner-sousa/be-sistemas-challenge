<?php

namespace App\Contracts;

use App\Models\BorrowedBook;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;

interface UpdateBorrowedBookQuantity extends ShouldDispatchAfterCommit
{
    public function getBorrowedBook(): BorrowedBook;
}
