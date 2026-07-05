<?php

namespace App\Http\Controllers\Api;

use App\Data\BorrowedBookData;
use App\Exceptions\BookUnavailableException;
use App\Exceptions\BorrowLimitExceededException;
use App\Exceptions\LoanAlreadyReturnedException;
use App\Exceptions\LoanNotFoundException;
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
        try {
            foreach ($request->validated()['books'] as $bookId) {
                $this->borrowedBookService->addBook($bookId);
            }

            $this->borrowedBookService->commitBorrowBooks();
        } catch (BookUnavailableException $exception) {
            throw ValidationException::withMessages([
                'borrowed_books' => $exception->getMessage(),
            ]);
        } catch (BorrowLimitExceededException $exception) {
            throw ValidationException::withMessages([
                'borrowed_books' => $exception->getMessage(),
            ]);
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
        } catch (LoanAlreadyReturnedException $exception) {
            throw ValidationException::withMessages([
                'borrowed_book' => $exception->getMessage(),
            ]);
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
        } catch (LoanNotFoundException $exception) {
            throw ValidationException::withMessages([
                'identifier' => $exception->getMessage(),
            ]);
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
