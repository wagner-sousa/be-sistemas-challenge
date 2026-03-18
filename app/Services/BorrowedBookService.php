<?php

namespace App\Services;

use App\Models\Book;
use App\Models\BorrowedBook;
use App\Models\User;
use App\Repositories\BorrowedBookRepository;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

use function collect;

/**
 * Class BorrowedBookService.
 */
final class BorrowedBookService
{
    private string $identifier;

    /** @var Collection<Book> */
    private Collection $books;

    public function __construct(
        private BorrowedBookRepository $borrowedBookRepository
    ) {
        $this->books = collect();
    }

    public function checkBookPreparedQuantity(): void {
        throw_if(
            $this->books->count() >= $this->getBorrowBooksLimit(),
            \Exception::class,
            'Limite de livros preparados para o empréstimo atingido!'
        );
    }

    public function addBook(int $bookId): void {
        throw_if(
            $this->books->has($bookId),
            \Exception::class,
            'Livro já adicionado para empréstimo.'
        );

        $this->checkBookPreparedQuantity();
        $book = Book::query()->findOrFail($bookId);
        $this->checkBookAvailable($book);
        $this->books->put($book->id, $book);
    }

    public function removeBook(int $bookId): void {
        $this->books->forget($bookId);
    }

    private function generateIdentifier(): void {
        $this->identifier = (string) Str::uuid();
    }

    public function commitBorrowBooks(): void {
        throw_if(
            $this->books->isEmpty(),
            \Exception::class,
            'Selecione ao menos um livro para emprestar.'
        );

        $this->checkBorrowedBookByUser();

        DB::transaction(function (): void {
            $this->generateIdentifier();

            $this->books->each(function (Book $book): void {
                $lockedBook = Book::query()
                    ->whereKey($book->getKey())
                    ->lockForUpdate()
                    ->firstOrFail();

                $this->checkBookAvailable($lockedBook);
                $this->borrowedBookRepository->create([
                    'book_id' => $book->id,
                    'identifier' => $this->getIdentifier(),
                ]);
            });
        });

        $this->resetPreparedBooks();
    }

    public function checkBorrowedBookByUser(): void {
        /** @var User $user */
        $user = Auth::user();

        throw_if(is_null($user), \Exception::class, 'Usuário não autenticado.');

        $limit = $this->getBorrowBooksLimit();
        $requestedBooks = $this->books->count();

        throw_if(
            ($user->current_borrowed_books + $requestedBooks) > $limit,
            \Exception::class,
            sprintf(
                'Limite de livros emprestados atingido, o usuário já possui %d livros emprestados!',
                $user->current_borrowed_books
            )
        );
    }

    private function checkBookAvailable(Book $book): void {
        throw_if(
            $book->refresh()->available_quantity <= 0,
            \Exception::class,
            'Quantidade de livros disponíveis insuficiente!'
        );
    }

    public function returnAllBooks(string $identifier): void {
        $borrowedBooks = $this->borrowedBookRepository->getByIdentifier($identifier);

        throw_if(
            $borrowedBooks->isEmpty(),
            \Exception::class,
            'Nenhum livro encontrado para devolução com o identificador fornecido!'
        );

        DB::transaction(function () use ($borrowedBooks): void {
            $borrowedBooks->each(function (BorrowedBook $borrowedBook): void {
                $this->returnBook($borrowedBook);
            });
        });
    }

    public function returnBook(BorrowedBook $borrowedBook): void {
        throw_if(
            !is_null($borrowedBook->ended_at),
            \Exception::class,
            'Este empréstimo já foi finalizado.',
        );

        DB::transaction(function () use ($borrowedBook): void {
            $borrowedBook->update([
                'ended_at' => now(),
            ]);
        });
    }

    public function getIdentifier(): string {
        return $this->identifier;
    }

    public function setIdentifier(string $identifier): self {
        $this->identifier = $identifier;

        return $this;
    }

    public function getBooks(): Collection {
        return $this->books;
    }

    private function getBorrowBooksLimit(): int {
        return (int) config('library.borrowed_books_limit', 3);
    }

    private function resetPreparedBooks(): void {
        $this->books = collect();
    }
}
