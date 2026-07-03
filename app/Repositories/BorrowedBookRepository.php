<?php

namespace App\Repositories;

use App\Models\BorrowedBook;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use JasonGuru\LaravelMakeRepository\Repository\BaseRepository;

/**
 * Class BorrowedBookRepository.
 */
class BorrowedBookRepository extends BaseRepository
{
    /**
     * @return string
     *                Return the model
     */
    public function model()
    {
        return BorrowedBook::class;
    }

    public function getByIdentifier(string $identifier): Collection
    {
        return BorrowedBook::query()
            ->where('identifier', $identifier)
            ->whereNull('ended_at')
            ->where('user_id', Auth::id())
            ->get();
    }

    /**
     * Lista empréstimos do usuário autenticado usando SQL Raw com JOIN.
     *
     * @return array<int, object>
     */
    public function getUserLoansWithRawSql(int $userId): array
    {
        $sql = "
            SELECT
                bb.id,
                bb.identifier,
                bb.book_id,
                bb.started_at,
                bb.ended_at,
                b.title,
                b.isbn_code,
                b.total_quantity,
                b.borrowed_quantity,
                b.active,
                a.name AS author,
                DATE_ADD(bb.started_at, INTERVAL :duration DAY) AS predicted_end_at,
                CASE
                    WHEN bb.ended_at IS NULL AND DATE_ADD(bb.started_at, INTERVAL :duration2 DAY) < NOW() THEN 1
                    ELSE 0
                END AS is_overdue
            FROM borrowed_books bb
            INNER JOIN books b ON bb.book_id = b.id
            INNER JOIN authors a ON b.author_id = a.id
            WHERE bb.user_id = :user_id
            ORDER BY bb.created_at DESC
        ";

        $duration = (int) config('library.borrowed_book_duration', 7);

        return DB::select($sql, [
            'duration' => $duration,
            'duration2' => $duration,
            'user_id' => $userId,
        ]);
    }
}
