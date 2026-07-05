<?php

namespace App\Http\Controllers\Api;

use App\Data\BorrowedBookData;
use App\Http\Controllers\Controller;
use App\Http\Requests\BorrowBooksRequest;
use App\Models\BorrowedBook;
use App\Repositories\BorrowedBookRepository;
use App\Services\BorrowedBookService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class BorrowedBookController extends Controller
{
    public function __construct(
        private BorrowedBookService $borrowedBookService,
        private BorrowedBookRepository $borrowedBookRepository
    ) {}

    public function index(Request $request): JsonResponse
    {
        $loans = $this->borrowedBookRepository->getUserLoansWithRawSql($request->user()->id);

        return response()->json(['data' => $loans]);
    }

    public function store(BorrowBooksRequest $request): JsonResponse {
        $idempotencyKey = $request->validated('idempotency_key');

        // Check if this request was already processed
        if ($idempotencyKey) {
            $existingLoan = BorrowedBook::where('idempotency_key', $idempotencyKey)
                ->where('user_id', Auth::id())
                ->first();

            if ($existingLoan) {
                return response()
                    ->json([
                        'identifier' => $existingLoan->identifier,
                        'idempotency_key' => $idempotencyKey,
                        'duplicate' => true,
                    ])
                    ->setStatusCode(Response::HTTP_OK);
            }
        }

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

        $identifier = $this->borrowedBookService->getIdentifier();

        // Store idempotency key if provided
        if ($idempotencyKey) {
            BorrowedBook::where('identifier', $identifier)
                ->update(['idempotency_key' => $idempotencyKey]);
        }

        return response()
            ->json([
                'identifier' => $identifier,
                'idempotency_key' => $idempotencyKey,
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
