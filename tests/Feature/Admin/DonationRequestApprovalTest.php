<?php

namespace Tests\Feature\Admin;

use App\Models\DeliveryConfirmation;
use App\Models\DonationRequest;
use App\Models\Glasses;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class DonationRequestApprovalTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function admin_cannot_approve_request_without_confirmed_receipt(): void
    {
        $admin = User::factory()->admin()->create();
        $donationRequest = DonationRequest::factory()->create(['status' => 'pending']);

        $this->actingAs($admin)
            ->post(route('admin.donation_requests.approve', $donationRequest))
            ->assertRedirect();

        $this->assertDatabaseHas('donation_requests', [
            'id' => $donationRequest->id,
            'status' => 'pending', // لم تتغير
        ]);
    }

    #[Test]
    public function admin_can_approve_request_after_recipient_confirms_receipt(): void
    {
        Notification::fake();

        $admin = User::factory()->admin()->create();
        $donationRequest = DonationRequest::factory()->create(['status' => 'pending']);

        DeliveryConfirmation::factory()->received()->create([
            'donation_request_id' => $donationRequest->id,
            'glasses_id' => $donationRequest->glasses_id,
            'donor_id' => $donationRequest->donor_id,
            'recipient_id' => $donationRequest->recipient_id,
        ]);

        $this->actingAs($admin)
            ->post(route('admin.donation_requests.approve', $donationRequest), [
                'admin_note' => 'تمت المراجعة والموافقة',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('donation_requests', [
            'id' => $donationRequest->id,
            'status' => 'approved',
        ]);
    }

    #[Test]
    public function admin_can_reject_a_pending_donation_request(): void
    {
        $admin = User::factory()->admin()->create();
        $donationRequest = DonationRequest::factory()->create(['status' => 'pending']);

        $this->actingAs($admin)
            ->post(route('admin.donation_requests.reject', $donationRequest), [
                'admin_note' => 'مستندات ناقصة',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('donation_requests', [
            'id' => $donationRequest->id,
            'status' => 'rejected',
        ]);
    }

    #[Test]
    public function approve_and_reject_endpoints_are_rate_limited(): void
    {
        $admin = User::factory()->admin()->create();
        $donationRequest = DonationRequest::factory()->create(['status' => 'pending']);

        for ($i = 0; $i < 20; $i++) {
            $this->actingAs($admin)
                ->post(route('admin.donation_requests.reject', $donationRequest));
        }

        // الطلب رقم 21 يجب أن يُحجب بواسطة throttle:20,1
        $this->actingAs($admin)
            ->post(route('admin.donation_requests.reject', $donationRequest))
            ->assertStatus(429);
    }
}
