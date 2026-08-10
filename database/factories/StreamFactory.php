<?php

namespace Database\Factories;

use App\Models\Stream;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Stream>
 */
class StreamFactory extends Factory
{
    protected $model = Stream::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->randomElement(['Science', 'Commerce', 'Arts', 'Technology']);

        return [
            'name' => $name,
            'code' => strtoupper(Str::substr($name, 0, 3)).fake()->unique()->numerify('##'),
        ];
    }
}
