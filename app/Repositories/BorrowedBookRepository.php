<?php

namespace App\Repositories;

use App\Models\BorrowedBook;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use JasonGuru\LaravelMakeRepository\Repository\BaseRepository;
//use Your Model

/**
 * Class BorrowedBookRepository.
 */
class BorrowedBookRepository extends BaseRepository
{
    /**
     * @return string
     *  Return the model
     */
    public function model() {
        return BorrowedBook::class;
    }

    public function getByIdentifier(string $identifier): Collection {
        return $this->where('identifier', $identifier)
                    ->where('ended_at', null)
                    ->where('user_id', Auth::id())
                    ->get();
    }
}
