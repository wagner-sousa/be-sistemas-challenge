<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    /** @use HasFactory<\Database\Factories\BookFactory> */
    use HasFactory;

    protected $casts = [
        'total_quantity' => 'integer',
        'borrowed_quantity' => 'integer',
        'active' => 'boolean',
    ];

    protected $appends = [
        'available_quantity',
    ];

    public function author() {
        return $this->belongsTo(Author::class);
    }

    public function getAvailableQuantityAttribute() {
        return $this->total_quantity - $this->borrowed_quantity;
    }
}
