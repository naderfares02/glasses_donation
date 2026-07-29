<?php

namespace Database\Factories;

use App\Models\Conversation;
use App\Models\ContactRequest;
use App\Models\Glasses;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Conversation>
 */
class ConversationFactory extends Factory
{
    protected $model = Conversation::class;

    public function definition(): array
    {
        return [
            'contact_request_id' => ContactRequest::factory(),
            'glasses_id' => Glasses::factory(),
            'donor_id' => User::factory()->donor(),
            'recipient_id' => User::factory()->recipient(),
            'status' => 'open',
        ];
    }

    public function closed(): static
    {
        return $this->state(fn () => ['status' => 'closed']);
    }
}
