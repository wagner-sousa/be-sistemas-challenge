<?php

namespace Database\Factories;

use App\Models\Author;
use App\Models\Book;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Book>
 */
class BookFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence($this->faker->randomDigitNotZero()),
            'author_id' => Author::factory(),
            'isbn_code' => $this->faker->unique()->isbn13(),
            'total_quantity' => $this->faker->randomDigitNotZero(),
            'borrowed_quantity' => 0,
        ];
    }
}
