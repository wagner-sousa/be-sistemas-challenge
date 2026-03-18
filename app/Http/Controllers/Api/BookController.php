<?php

namespace App\Http\Controllers\Api;

use App\Data\BookData;
use App\Http\Controllers\Controller;
use App\Http\Requests\BookRequest;
use App\Models\Author;
use App\Models\Book;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;

class BookController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $books = Book::query()
            ->with('author')
            ->latest()
            ->paginate();

        $books->through(fn (Book $book) => BookData::fromModel($book)->toArray());

        return response()->json($books);
    }

    public function store(BookRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $author = $this->resolveAuthor($validated['author_name']);

        $book = Book::query()->create([
            'title' => $validated['title'],
            'author_id' => $author->id,
            'isbn_code' => $validated['isbn_code'],
            'total_quantity' => $validated['total_quantity'],
            'borrowed_quantity' => 0,
            'active' => $validated['active'] ?? true,
        ]);

        $book->load('author');

        return BookData::fromModel($book)
            ->toResponse($request)
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function show(Request $request, Book $book): JsonResponse
    {
        $book->load('author');

        return BookData::fromModel($book)->toResponse($request);
    }

    public function update(BookRequest $request, Book $book): JsonResponse
    {
        $validated = $request->validated();

        if (isset($validated['total_quantity']) && $validated['total_quantity'] < $book->borrowed_quantity) {
            throw ValidationException::withMessages([
                'total_quantity' => 'A quantidade total não pode ser menor que a quantidade emprestada atual.',
            ]);
        }

        if (isset($validated['author_name'])) {
            $author = $this->resolveAuthor($validated['author_name']);
            $validated['author_id'] = $author->id;
        }

        unset($validated['author_name']);

        $book->fill($validated);
        $book->save();
        $book->load('author');

        return BookData::fromModel($book)->toResponse($request);
    }

    public function destroy(Book $book): Response
    {
        if ($book->borrowed_quantity > 0) {
            throw ValidationException::withMessages([
                'book' => 'Não é possível remover um livro com empréstimos ativos.',
            ]);
        }

        $book->delete();

        return response()->noContent();
    }

    private function resolveAuthor(string $name): Author
    {
        $trimmed = trim($name);

        return Author::query()->firstOrCreate(['name' => $trimmed]);
    }
}
