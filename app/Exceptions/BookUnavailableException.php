<?php

namespace App\Exceptions;

use Exception;

class BookUnavailableException extends Exception
{
    public function __construct(string $bookTitle)
    {
        parent::__construct("O livro '{$bookTitle}' não possui cópias disponíveis no momento.");
    }
}
