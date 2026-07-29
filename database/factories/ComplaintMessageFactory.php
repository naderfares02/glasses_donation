<?php

namespace Database\Factories;

use App\Models\Complaint;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ComplaintMessage>
 */
class ComplaintMessageFactory extends Factory
{
    public function definition(): array
    {
        return [
            'complaint_id' => Complaint::factory(),
            'sender_id'    => User::factory(),
            'sender_role'  => 'user',
            'body'         => $this->faker->sentence(),
            'read_at'      => null,
        ];
    }

    public function fromAdmin(): static
    {
        return $this->state(fn () => [
            'sender_role' => 'admin',
        ]);
    }

    public function fromUser(): static
    {
        return $this->state(fn () => [
            'sender_role' => 'user',
        ]);
    }
}
