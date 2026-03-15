<?php

namespace Database\Factories;

use App\Models\Book;
use App\Models\BorrowedBook;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BorrowedBook>
 */
class BorrowedBookFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'book_id' => Book::factory(),
            'user_id' => User::factory(),
            'identifier' => $this->faker->uuid(),
            'started_at' => $this->faker->dateTime('now'),
            'ended_at' => $this->faker->optional()->dateTime('now'),
        ];
    }
}
