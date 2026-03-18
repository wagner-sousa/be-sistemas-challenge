<?php

namespace App\Contracts;

use App\Models\User;

interface UpdateUserBorrowedBookQuantity extends UpdateBorrowedBookQuantity
{
    public function getUser(): User;
}
