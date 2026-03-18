<?php

namespace App\Models;

use Database\Factories\BookFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    /** @use HasFactory<BookFactory> */
    use HasFactory;

    protected $casts = [
        'total_quantity' => 'integer',
        'borrowed_quantity' => 'integer',
        'active' => 'boolean',
    ];

    protected $fillable = [
        'title',
        'author_id',
        'isbn_code',
        'total_quantity',
        'borrowed_quantity',
        'active',
    ];

    protected $appends = [
        'available_quantity',
    ];

    public function author()
    {
        return $this->belongsTo(Author::class);
    }

    public function getAvailableQuantityAttribute()
    {
        return $this->total_quantity - $this->borrowed_quantity;
    }
}
