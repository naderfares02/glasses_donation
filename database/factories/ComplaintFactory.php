<?php

namespace Database\Factories;

use App\Models\Conversation;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Complaint>
 */
class ComplaintFactory extends Factory
{
    public function definition(): array
    {
        $conversation = Conversation::factory()->create();

        return [
            'conversation_id'  => $conversation->id,
            'glasses_id'       => $conversation->glasses_id,
            'reporter_id'      => $conversation->donor_id,
            'reported_user_id' => $conversation->recipient_id,
            'reason'           => $this->faker->randomElement(['no_response', 'spam', 'inappropriate_behavior', 'other']),
            'description'      => $this->faker->optional()->sentence(),
            'status'           => 'open',
            'handled_by'       => null,
            'resolution_note'  => null,
        ];
    }

    public function reviewing(): static
    {
        return $this->state(fn () => ['status' => 'reviewing']);
    }

    public function resolved(): static
    {
        return $this->state(fn () => ['status' => 'resolved']);
    }

    public function dismissed(): static
    {
        return $this->state(fn () => ['status' => 'dismissed']);
    }
}
