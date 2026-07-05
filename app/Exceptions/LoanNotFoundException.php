<?php

namespace App\Exceptions;

use Exception;

class LoanNotFoundException extends Exception
{
    public function __construct()
    {
        parent::__construct('Nenhum empréstimo encontrado com o identificador fornecido.');
    }
}
