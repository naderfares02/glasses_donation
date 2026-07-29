<?php

namespace Database\Factories;

use App\Models\DeliveryConfirmation;
use App\Models\DonationRequest;
use App\Models\Glasses;
use App\Models\Conversation;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\DeliveryConfirmation>
 */
class DeliveryConfirmationFactory extends Factory
{
    protected $model = DeliveryConfirmation::class;

    public function definition(): array
    {
        return [
            'donation_request_id' => DonationRequest::factory(),
            'glasses_id' => Glasses::factory(),
            'conversation_id' => Conversation::factory(),
            'donor_id' => User::factory()->donor(),
            'recipient_id' => User::factory()->recipient(),
            'status' => 'pending',
        ];
    }

    public function received(): static
    {
        return $this->state(fn () => [
            'status' => 'received',
            'recipient_responded_at' => now(),
        ]);
    }

    public function notReceived(): static
    {
        return $this->state(fn () => [
            'status' => 'not_received',
            'recipient_responded_at' => now(),
        ]);
    }
}
