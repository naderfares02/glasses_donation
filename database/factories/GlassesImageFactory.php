<?php

namespace Database\Factories;

use App\Models\Glasses;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\GlassesImage>
 */
class GlassesImageFactory extends Factory
{
    public function definition(): array
    {
        return [
            'glasses_id' => Glasses::factory(),
            'path'       => 'glasses/' . $this->faker->uuid() . '.jpg',
            'is_primary' => false,
        ];
    }

    public function primary(): static
    {
        return $this->state(fn () => ['is_primary' => true]);
    }
}
