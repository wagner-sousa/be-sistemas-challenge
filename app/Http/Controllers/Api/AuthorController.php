<?php

namespace App\Http\Controllers\Api;

use App\Data\AuthorData;
use App\Http\Controllers\Controller;
use App\Http\Requests\AuthorRequest;
use App\Models\Author;
use App\Models\Book;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;

class AuthorController extends Controller
{
    public function index(Request $request): JsonResponse {
        $authors = Author::query()
            ->latest()
            ->paginate();

        $authors->through(fn (Author $author) => AuthorData::fromModel($author)->toArray());

        return response()->json($authors);
    }

    public function store(AuthorRequest $request): JsonResponse {
        $author = Author::query()->create($request->validated());

        return AuthorData::fromModel($author)
            ->toResponse($request)
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function show(Request $request, Author $author): JsonResponse {
        return AuthorData::fromModel($author)->toResponse($request);
    }

    public function update(AuthorRequest $request, Author $author): JsonResponse {
        $author->update($request->validated());

        return AuthorData::fromModel($author)->toResponse($request);
    }

    public function destroy(Author $author): Response {
        $hasBooks = Book::query()->where('author_id', $author->id)->exists();

        if ($hasBooks) {
            throw ValidationException::withMessages([
                'author' => 'Não é possível remover um autor com livros associados.',
            ]);
        }

        $author->delete();

        return response()->noContent();
    }
}
