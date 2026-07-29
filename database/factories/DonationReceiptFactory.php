<?php

namespace Database\Factories;

use App\Models\DonationRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\DonationReceipt>
 */
class DonationReceiptFactory extends Factory
{
    public function definition(): array
    {
        $donationRequest = DonationRequest::factory()->create();

        return [
            'donation_request_id' => $donationRequest->id,
            'glasses_id'          => $donationRequest->glasses_id,
            'donor_id'            => $donationRequest->donor_id,
            'recipient_id'        => $donationRequest->recipient_id,
            'approved_by'         => User::factory()->admin(),
            'delivered_date'      => $this->faker->dateTimeBetween('-1 month', 'now'),
            'admin_note'          => $this->faker->optional()->sentence(),
            'receipt_code'        => strtoupper($this->faker->unique()->bothify('RCPT-########')),
            'pdf_path'            => null,
            'issued_at'           => now(),
        ];
    }
}
