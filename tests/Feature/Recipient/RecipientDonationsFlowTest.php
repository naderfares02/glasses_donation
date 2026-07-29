<?php

namespace Tests\Feature\Recipient;

use App\Models\DeliveryConfirmation;
use App\Models\DonationRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class RecipientDonationsFlowTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function recipient_can_view_their_pending_confirmations(): void
    {
        $recipient = User::factory()->recipient()->create();

        DeliveryConfirmation::factory()->create([
            'recipient_id' => $recipient->id,
            'status' => 'pending',
        ]);

        $this->actingAs($recipient)
            ->get(route('recipient.donations.index'))
            ->assertOk();
    }

    #[Test]
    public function recipient_cannot_view_someone_elses_confirmation(): void
    {
        $recipient = User::factory()->recipient()->create();
        $otherRecipient = User::factory()->recipient()->create();

        $confirmation = DeliveryConfirmation::factory()->create([
            'recipient_id' => $otherRecipient->id,
        ]);

        $this->actingAs($recipient)
            ->get(route('recipient.confirmations.show', $confirmation))
            ->assertForbidden();
    }

    #[Test]
    public function recipient_can_mark_a_pending_confirmation_as_received(): void
    {
        Notification::fake();

        $recipient = User::factory()->recipient()->create();
        $admin = User::factory()->admin()->create();

        $donationRequest = DonationRequest::factory()->create();
        $confirmation = DeliveryConfirmation::factory()->create([
            'recipient_id' => $recipient->id,
            'donation_request_id' => $donationRequest->id,
            'status' => 'pending',
        ]);

        $this->actingAs($recipient)
            ->post(route('recipient.confirmations.received', $confirmation), [
                'recipient_note' => 'وصلتني بحالة ممتازة',
            ])
            ->assertRedirect(route('recipient.donations.index', ['tab' => 'received']));

        $this->assertDatabaseHas('delivery_confirmations', [
            'id' => $confirmation->id,
            'status' => 'received',
            'recipient_note' => 'وصلتني بحالة ممتازة',
        ]);

        Notification::assertSentTo($admin, \App\Notifications\AdminRecipientConfirmedDeliveryNotification::class);
    }

    #[Test]
    public function recipient_cannot_mark_received_twice(): void
    {
        $recipient = User::factory()->recipient()->create();

        $confirmation = DeliveryConfirmation::factory()->create([
            'recipient_id' => $recipient->id,
            'status' => 'received', // already resolved
        ]);

        $this->actingAs($recipient)
            ->post(route('recipient.confirmations.received', $confirmation))
            ->assertRedirect();

        // لسه لازم تفضل received زي ما هي (ما اتغيرش وقت الرد)
        $this->assertDatabaseHas('delivery_confirmations', [
            'id' => $confirmation->id,
            'status' => 'received',
        ]);
    }

    #[Test]
    public function recipient_cannot_confirm_someone_elses_delivery(): void
    {
        $recipient = User::factory()->recipient()->create();
        $otherRecipient = User::factory()->recipient()->create();

        $confirmation = DeliveryConfirmation::factory()->create([
            'recipient_id' => $otherRecipient->id,
            'status' => 'pending',
        ]);

        $this->actingAs($recipient)
            ->post(route('recipient.confirmations.received', $confirmation))
            ->assertForbidden();
    }

    #[Test]
    public function marking_not_received_requires_a_note(): void
    {
        $recipient = User::factory()->recipient()->create();

        $confirmation = DeliveryConfirmation::factory()->create([
            'recipient_id' => $recipient->id,
            'status' => 'pending',
        ]);

        $this->actingAs($recipient)
            ->post(route('recipient.confirmations.not_received', $confirmation), [])
            ->assertSessionHasErrors(['recipient_note']);

        $this->assertDatabaseHas('delivery_confirmations', [
            'id' => $confirmation->id,
            'status' => 'pending', // لسه ما اتغيّرتش
        ]);
    }

    #[Test]
    public function recipient_can_mark_a_pending_confirmation_as_not_received(): void
    {
        $recipient = User::factory()->recipient()->create();

        $confirmation = DeliveryConfirmation::factory()->create([
            'recipient_id' => $recipient->id,
            'status' => 'pending',
        ]);

        $this->actingAs($recipient)
            ->post(route('recipient.confirmations.not_received', $confirmation), [
                'recipient_note' => 'لسه ما وصلنيش حاجة',
            ])
            ->assertRedirect(route('recipient.donations.index', ['tab' => 'not_received']));

        $this->assertDatabaseHas('delivery_confirmations', [
            'id' => $confirmation->id,
            'status' => 'not_received',
            'recipient_note' => 'لسه ما وصلنيش حاجة',
        ]);
    }
}
