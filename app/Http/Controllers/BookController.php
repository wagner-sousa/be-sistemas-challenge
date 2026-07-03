<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;

class BookController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Books/Index');
    }
}
