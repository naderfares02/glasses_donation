<?php

namespace Database\Factories;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Message>
 */
class MessageFactory extends Factory
{
    protected $model = Message::class;

    public function definition(): array
    {
        return [
            'conversation_id' => Conversation::factory(),
            'sender_id' => User::factory(),
            'body' => fake()->sentence(),
            'read_at' => null,
        ];
    }

    /**
     * رسالة مقروءة.
     */
    public function read(): static
    {
        return $this->state(fn () => ['read_at' => now()]);
    }
}
