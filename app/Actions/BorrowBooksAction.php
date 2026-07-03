<?php

namespace App\Actions;

use App\Models\BorrowedBook;
use App\Models\User;
use App\Repositories\BorrowedBookRepository;
use App\Services\BorrowedBookService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BorrowBooksAction
{
    private string $identifier;

    /** @var array<int, \App\Models\Book> */
    private array $books = [];

    public function __construct(
        private BorrowedBookRepository $borrowedBookRepository,
        private BorrowedBookService $borrowedBookService
    ) {
    }

    public function addBook(int $bookId): void
    {
        $this->borrowedBookService->addBook($bookId);
    }

    public function execute(): string
    {
        $this->borrowedBookService->commitBorrowBooks();

        return $this->borrowedBookService->getIdentifier();
    }
}
