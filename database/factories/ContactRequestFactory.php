<?php

namespace Database\Factories;

use App\Models\ContactRequest;
use App\Models\Glasses;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ContactRequest>
 */
class ContactRequestFactory extends Factory
{
    protected $model = ContactRequest::class;

    public function definition(): array
    {
        return [
            'glasses_id' => Glasses::factory(),
            'donor_id' => User::factory()->donor(),
            'recipient_id' => User::factory()->recipient(),
            'status' => 'pending',
        ];
    }

    public function accepted(): static
    {
        return $this->state(fn () => [
            'status' => 'accepted',
            'accepted_at' => now(),
        ]);
    }

    public function rejected(): static
    {
        return $this->state(fn () => ['status' => 'rejected']);
    }
}
