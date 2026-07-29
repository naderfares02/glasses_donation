<?php

namespace Database\Factories;

use App\Models\Glasses;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Glasses>
 */
class GlassesFactory extends Factory
{
    protected $model = Glasses::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory()->donor(),
            'title' => fake()->sentence(3),
            'description' => fake()->paragraph(),
            'condition' => fake()->randomElement(Glasses::CONDITIONS),
            'status' => 'available',
            'brand' => fake()->company(),
            'lens_type' => fake()->randomElement(Glasses::LENS_TYPES),
            'vision_type' => fake()->randomElement(Glasses::VISION_TYPES),
            'sph' => '-1.00',
            'cyl' => '-0.50',
            'axis' => '90',
            'pd' => '62',
            'frame_size' => fake()->randomElement(Glasses::FRAME_SIZES),
            'frame_color' => fake()->safeColorName(),
            'age_group' => fake()->randomElement(Glasses::AGE_GROUPS),
            'gender' => fake()->randomElement(Glasses::GENDERS),
            'pickup_city' => fake()->city(),
            'contact_method' => fake()->randomElement(Glasses::CONTACT_METHODS),
        ];
    }

    public function available(): static
    {
        return $this->state(fn () => ['status' => 'available']);
    }

    public function donated(): static
    {
        return $this->state(fn () => ['status' => 'donated']);
    }

    public function reserved(): static
    {
        return $this->state(fn () => ['status' => 'reserved']);
    }
}
