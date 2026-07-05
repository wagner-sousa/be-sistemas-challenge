<?php

namespace App\Actions;

use App\Models\BorrowedBook;
use App\Services\BorrowedBookService;

class ReturnBooksAction
{
    public function __construct(
        private BorrowedBookService $borrowedBookService
    ) {
    }

    public function returnSingle(BorrowedBook $borrowedBook): void
    {
        $this->borrowedBookService->returnBook($borrowedBook);
    }

    public function returnByIdentifier(string $identifier): void
    {
        $this->borrowedBookService->returnAllBooks($identifier);
    }
}
