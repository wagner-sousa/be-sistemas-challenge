<?php

namespace App\Http\Controllers\Api;

use App\Data\BorrowedBookData;
use App\Http\Controllers\Controller;
use App\Http\Requests\BorrowBooksRequest;
use App\Models\BorrowedBook;
use App\Services\BorrowedBookService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;

class BorrowedBookController extends Controller
{
    public function __construct(private BorrowedBookService $borrowedBookService) {}

    public function index(Request $request): JsonResponse {
        $borrowedBooks = BorrowedBook::query()
            ->with(['book.author', 'user'])
            ->where('user_id', $request->user()->id)
            ->latest()
            ->paginate();

        $borrowedBooks->through(fn (BorrowedBook $borrowedBook) => BorrowedBookData::fromModel($borrowedBook)->toArray());

        return response()->json($borrowedBooks);
    }

    public function store(BorrowBooksRequest $request): JsonResponse {
        try {
            foreach ($request->validated()['books'] as $bookId) {
                $this->borrowedBookService->addBook($bookId);
            }

            $this->borrowedBookService->commitBorrowBooks();
        } catch (\Exception $exception) {
            throw ValidationException::withMessages([
                'borrowed_books' => $exception->getMessage(),
            ]);
        }

        return response()
            ->json([
                'identifier' => $this->borrowedBookService->getIdentifier(),
            ])
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function show(Request $request, BorrowedBook $borrowedBook): JsonResponse {
        $borrowedBook->load(['book.author', 'user']);

        return BorrowedBookData::fromModel($borrowedBook)->toResponse($request);
    }

    public function returnBorrowedBook(Request $request, BorrowedBook $borrowedBook): JsonResponse {
        try {
            $this->borrowedBookService->returnBook($borrowedBook);
        } catch (\Exception $exception) {
            throw ValidationException::withMessages([
                'borrowed_book' => $exception->getMessage(),
            ]);
        }

        $borrowedBook->load(['book.author', 'user']);

        return BorrowedBookData::fromModel($borrowedBook)->toResponse($request);
    }

    public function returnBooksByIdentifier(string $identifier): JsonResponse {
        try {
            $this->borrowedBookService->returnAllBooks($identifier);
        } catch (\Exception $exception) {
            throw ValidationException::withMessages([
                'identifier' => $exception->getMessage(),
            ]);
        }

        return response()->json([
            'message' => 'Livros devolvidos com sucesso.',
        ]);
    }
}
