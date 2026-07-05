<?php

namespace App\Exceptions;

use Exception;

class BorrowLimitExceededException extends Exception
{
    public function __construct(int $current, int $limit)
    {
        parent::__construct("Você já possui {$current} livros emprestados. O limite é de {$limit} empréstimos ativos.");
    }
}
