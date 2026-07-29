<?php

namespace Tests\Feature\Recipient;

use App\Models\Glasses;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class GlassesShowTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function recipient_can_view_an_available_glasses_listing(): void
    {
        $recipient = User::factory()->recipient()->create();
        $glasses = Glasses::factory()->available()->create();

        $this->actingAs($recipient)
            ->get(route('recipient.glasses.show', $glasses))
            ->assertOk();
    }

    #[Test]
    public function donor_cannot_access_the_recipient_glasses_page(): void
    {
        $donor = User::factory()->donor()->create();
        $glasses = Glasses::factory()->available()->create();

        $this->actingAs($donor)
            ->get(route('recipient.glasses.show', $glasses))
            ->assertForbidden();
    }

    #[Test]
    public function guest_cannot_view_a_glasses_listing(): void
    {
        $glasses = Glasses::factory()->available()->create();

        $this->get(route('recipient.glasses.show', $glasses))
            ->assertRedirect(route('login'));
    }

    #[Test]
    public function recipient_cannot_view_a_glasses_listing_that_is_already_donated_to_someone_else(): void
    {
        // ⚠️ The controller's own comment says "only glasses available to the
        // recipient", but there was no status check in Recipient\GlassesController::show().
        // A recipient with no connection to this listing should not be able to open
        // it once it's no longer available.
        $recipient = User::factory()->recipient()->create();
        $glasses = Glasses::factory()->create(['status' => 'donated']);

        $this->actingAs($recipient)
            ->get(route('recipient.glasses.show', $glasses))
            ->assertForbidden();
    }

    #[Test]
    public function recipient_can_still_view_a_non_available_glasses_listing_they_have_a_contact_request_for(): void
    {
        // Regression guard: recipients who are genuinely mid-flow (e.g. viewing
        // from their donations/delivery-confirmation pages) must not be locked out
        // just because the glasses moved on from "available".
        $recipient = User::factory()->recipient()->create();
        $glasses = Glasses::factory()->create(['status' => 'in_contact']);

        \App\Models\ContactRequest::factory()->create([
            'glasses_id'   => $glasses->id,
            'recipient_id' => $recipient->id,
        ]);

        $this->actingAs($recipient)
            ->get(route('recipient.glasses.show', $glasses))
            ->assertOk();
    }

    #[Test]
    public function recipient_can_still_view_a_donated_glasses_listing_they_have_a_donation_request_for(): void
    {
        $recipient = User::factory()->recipient()->create();
        $glasses = Glasses::factory()->create(['status' => 'donated']);

        \App\Models\DonationRequest::factory()->create([
            'glasses_id'   => $glasses->id,
            'recipient_id' => $recipient->id,
        ]);

        $this->actingAs($recipient)
            ->get(route('recipient.glasses.show', $glasses))
            ->assertOk();
    }
}
