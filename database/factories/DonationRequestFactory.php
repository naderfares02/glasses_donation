<?php

namespace Database\Factories;

use App\Models\DonationRequest;
use App\Models\Glasses;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\DonationRequest>
 */
class DonationRequestFactory extends Factory
{
    protected $model = DonationRequest::class;

    public function definition(): array
    {
        return [
            'glasses_id' => Glasses::factory(),
            'donor_id' => User::factory()->donor(),
            'recipient_id' => User::factory()->recipient(),
            'status' => 'pending',
        ];
    }

    public function approved(): static
    {
        return $this->state(fn () => [
            'status' => 'approved',
            'reviewed_at' => now(),
        ]);
    }

    public function rejected(): static
    {
        return $this->state(fn () => [
            'status' => 'rejected',
            'reviewed_at' => now(),
        ]);
    }
}
