<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;

class LoanController extends Controller
{
    public function myLoans(): Response
    {
        return Inertia::render('Loans/MyLoans');
    }
}
