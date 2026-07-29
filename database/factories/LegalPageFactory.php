<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\LegalPage>
 */
class LegalPageFactory extends Factory
{
    public function definition(): array
    {
        return [
            'key'          => $this->faker->randomElement(['terms', 'privacy']),
            'title'        => $this->faker->sentence(3),
            'content'      => '<p>' . $this->faker->paragraph() . '</p>',
            'updated_by'   => null,
            'published_at' => now(),
        ];
    }

    public function terms(): static
    {
        return $this->state(fn () => ['key' => 'terms', 'title' => 'Terms & Conditions']);
    }

    public function privacy(): static
    {
        return $this->state(fn () => ['key' => 'privacy', 'title' => 'Privacy Policy']);
    }

    public function unpublished(): static
    {
        return $this->state(fn () => ['published_at' => null]);
    }
}