<?php

namespace App\Exceptions;

use Exception;

class LoanAlreadyReturnedException extends Exception
{
    public function __construct()
    {
        parent::__construct('Este empréstimo já foi finalizado.');
    }
}
