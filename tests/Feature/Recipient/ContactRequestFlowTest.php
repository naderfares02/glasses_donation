<?php

namespace Tests\Feature\Recipient;

use App\Models\ContactRequest;
use App\Models\Glasses;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class ContactRequestFlowTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function recipient_can_send_a_contact_request_for_available_glasses(): void
    {
        $donor = User::factory()->donor()->create();
        $recipient = User::factory()->recipient()->create();
        $glasses = Glasses::factory()->available()->create(['user_id' => $donor->id]);

        $this->actingAs($recipient)
            ->post(route('recipient.contact-requests.store', $glasses))
            ->assertRedirect();

        $this->assertDatabaseHas('contact_requests', [
            'glasses_id' => $glasses->id,
            'recipient_id' => $recipient->id,
            'donor_id' => $donor->id,
            'status' => 'pending',
        ]);
    }

    #[Test]
    public function recipient_cannot_send_duplicate_pending_request_for_same_glasses(): void
    {
        $donor = User::factory()->donor()->create();
        $recipient = User::factory()->recipient()->create();
        $glasses = Glasses::factory()->available()->create(['user_id' => $donor->id]);

        ContactRequest::factory()->create([
            'glasses_id' => $glasses->id,
            'donor_id' => $donor->id,
            'recipient_id' => $recipient->id,
            'status' => 'pending',
        ]);

        $this->actingAs($recipient)
            ->post(route('recipient.contact-requests.store', $glasses));

        $this->assertEquals(
            1,
            ContactRequest::where('glasses_id', $glasses->id)
                ->where('recipient_id', $recipient->id)
                ->where('status', 'pending')
                ->count()
        );
    }

    #[Test]
    public function donor_can_accept_a_pending_contact_request(): void
    {
        $donor = User::factory()->donor()->create();
        $recipient = User::factory()->recipient()->create();
        $glasses = Glasses::factory()->available()->create(['user_id' => $donor->id]);

        $request = ContactRequest::factory()->create([
            'glasses_id' => $glasses->id,
            'donor_id' => $donor->id,
            'recipient_id' => $recipient->id,
            'status' => 'pending',
        ]);

        $this->actingAs($donor)
            ->post(route('donor.requests.accept', $request))
            ->assertRedirect();

        $this->assertDatabaseHas('contact_requests', [
            'id' => $request->id,
            'status' => 'accepted',
        ]);
    }

    #[Test]
    public function donor_can_reject_a_pending_contact_request(): void
    {
        $donor = User::factory()->donor()->create();
        $recipient = User::factory()->recipient()->create();
        $glasses = Glasses::factory()->available()->create(['user_id' => $donor->id]);

        $request = ContactRequest::factory()->create([
            'glasses_id' => $glasses->id,
            'donor_id' => $donor->id,
            'recipient_id' => $recipient->id,
            'status' => 'pending',
        ]);

        $this->actingAs($donor)
            ->post(route('donor.requests.reject', $request))
            ->assertRedirect();

        $this->assertDatabaseHas('contact_requests', [
            'id' => $request->id,
            'status' => 'rejected',
        ]);
    }

    #[Test]
    public function another_donor_cannot_accept_someone_elses_request(): void
    {
        $donor = User::factory()->donor()->create();
        $otherDonor = User::factory()->donor()->create();
        $recipient = User::factory()->recipient()->create();
        $glasses = Glasses::factory()->available()->create(['user_id' => $donor->id]);

        $request = ContactRequest::factory()->create([
            'glasses_id' => $glasses->id,
            'donor_id' => $donor->id,
            'recipient_id' => $recipient->id,
            'status' => 'pending',
        ]);

        $this->actingAs($otherDonor)
            ->post(route('donor.requests.accept', $request))
            ->assertForbidden();
    }
}
