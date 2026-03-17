<?php

namespace App\Models;

use App\Events;
use Database\Factories\BorrowedBookFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class BorrowedBook extends Model
{
    /** @use HasFactory<\Database\Factories\BorrowedBookFactory> */
    use HasFactory;

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
    ];

    protected $dates = [
        'started_at',
        'ended_at',
    ];

    protected $dispatchesEvents = [
        'created' => Events\BorrowedBookCreated::class,
        'updated' => Events\BorrowedBookUpdated::class,
    ];

    protected $fillable = [
        'book_id',
        'user_id',
        'identifier',
        'started_at',
        'ended_at',
    ];

    public static function boot() {
        parent::boot();

        static::creating(function ($book) {
            /** @var User $user */
            $user = Auth::user();

            $book->user_id = $user->id;
            $book->started_at = now();
        });
    }

    public function book() {
        return $this->belongsTo(Book::class);
    }

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function getPredictedEndAtAttribute() {
        return $this->started_at->addDays(env('BORROWED_BOOK_DURATION', 3));
    }

    public function getIsOverdueAttribute() {
        return $this->predicted_end_at->isPast() && is_null($this->ended_at);
    }
}
