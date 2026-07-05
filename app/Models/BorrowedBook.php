<?php

namespace App\Models;

use App\Data\BorrowedBookData;
use Database\Factories\BorrowedBookFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Spatie\LaravelData\WithData;

class BorrowedBook extends Model
{
    /** @use HasFactory<BorrowedBookFactory> */
    use HasFactory;

    use WithData;

    protected $dataClass = BorrowedBookData::class;

    protected $fillable = [
        'book_id',
        'user_id',
        'identifier',
        'idempotency_key',
        'started_at',
        'ended_at',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
    ];

    protected $dates = [
        'started_at',
        'ended_at',
    ];

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::creating(function (BorrowedBook $book): void {
            if (! $book->user_id && Auth::check()) {
                $book->user_id = Auth::id();
            }

            if (! $book->started_at) {
                $book->started_at = now();
            }
        });
    }

    public function book()
    {
        return $this->belongsTo(Book::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getPredictedEndAtAttribute()
    {
        $durationInDays = (int) config('library.borrowed_book_duration', 3);

        return $this->started_at?->addDays($durationInDays);
    }

    public function getIsOverdueAttribute()
    {
        return ($this->predicted_end_at?->isPast() ?? false) && is_null($this->ended_at);
    }
}
