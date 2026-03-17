<?php

namespace App\Repositories;

use App\Models\BorrowedBook;
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
}
