<?php

namespace Tests\Feature\Donor;

use App\Models\ContactRequest;
use App\Models\Conversation;
use App\Models\Glasses;
use App\Models\User;
use App\Notifications\AdminNewDonationRequestNotification;
use App\Notifications\RecipientMustConfirmDeliveryNotification;
use App\Services\SettingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class ContactRequestDisconnectAndMarkDonatedTest extends TestCase
{
    use RefreshDatabase;

    protected function donor(): User
    {
        return User::factory()->donor()->create();
    }

    protected function openConversationFor(User $donor): array
    {
        $recipient = User::factory()->recipient()->create();
        $glasses = Glasses::factory()->create(['user_id' => $donor->id, 'status' => 'reserved']);

        $contactRequest = ContactRequest::factory()->accepted()->create([
            'glasses_id' => $glasses->id,
            'donor_id' => $donor->id,
            'recipient_id' => $recipient->id,
        ]);

        $glasses->update(['active_contact_request_id' => $contactRequest->id]);

        $conversation = Conversation::factory()->create([
            'contact_request_id' => $contactRequest->id,
            'glasses_id' => $glasses->id,
            'donor_id' => $donor->id,
            'recipient_id' => $recipient->id,
            'status' => 'open',
        ]);

        return [$glasses, $contactRequest, $conversation, $recipient];
    }

    /*
    |--------------------------------------------------------------------------
    | disconnect()
    |--------------------------------------------------------------------------
    */

    #[Test]
    public function donor_can_disconnect_their_own_conversation(): void
    {
        $donor = $this->donor();
        [$glasses, $contactRequest, $conversation] = $this->openConversationFor($donor);

        $this->actingAs($donor)
            ->post(route('donor.conversations.disconnect', $conversation))
            ->assertRedirect(route('donor.chats.index', ['conversation' => $conversation->id]));

        $this->assertDatabaseHas('conversations', [
            'id' => $conversation->id,
            'status' => 'closed',
        ]);

        $this->assertDatabaseHas('contact_requests', [
            'id' => $contactRequest->id,
            'status' => 'closed',
        ]);

        $this->assertDatabaseHas('glasses', [
            'id' => $glasses->id,
            'status' => 'available',
            'active_contact_request_id' => null,
        ]);
    }

    #[Test]
    public function disconnect_reopens_other_requests_that_were_put_on_hold(): void
    {
        $donor = $this->donor();
        [$glasses, $contactRequest, $conversation] = $this->openConversationFor($donor);

        $onHold = ContactRequest::factory()->create([
            'glasses_id' => $glasses->id,
            'donor_id' => $donor->id,
            'status' => 'on_hold',
        ]);

        $this->actingAs($donor)
            ->post(route('donor.conversations.disconnect', $conversation));

        $this->assertDatabaseHas('contact_requests', [
            'id' => $onHold->id,
            'status' => 'pending',
        ]);
    }

    #[Test]
    public function donor_cannot_disconnect_a_conversation_belonging_to_another_donor(): void
    {
        $donor = $this->donor();
        $otherDonor = $this->donor();
        [, , $conversation] = $this->openConversationFor($otherDonor);

        $this->actingAs($donor)
            ->post(route('donor.conversations.disconnect', $conversation))
            ->assertForbidden();
    }

    #[Test]
    public function disconnecting_an_already_donated_glasses_does_not_revert_its_status(): void
    {
        $donor = $this->donor();
        [$glasses, , $conversation] = $this->openConversationFor($donor);

        $glasses->update(['status' => 'donated']);

        $this->actingAs($donor)
            ->post(route('donor.conversations.disconnect', $conversation));

        $this->assertDatabaseHas('glasses', [
            'id' => $glasses->id,
            'status' => 'donated',
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | markDonated()
    |--------------------------------------------------------------------------
    */

    #[Test]
    public function marking_donated_requires_admin_approval_by_default(): void
    {
        Notification::fake();
        $admin = User::factory()->admin()->create();
        $donor = $this->donor();
        [$glasses, , $conversation, $recipient] = $this->openConversationFor($donor);

        $response = $this->actingAs($donor)->post(
            route('donor.glasses.mark_donated', $glasses),
            [
                'conversation_id' => $conversation->id,
                'delivered_date' => now()->toDateString(),
                'donor_note' => 'تم التسليم يدوياً',
            ]
        );

        $response->assertRedirect(route('donor.chats.index', ['conversation' => $conversation->id]));

        $this->assertDatabaseHas('glasses', [
            'id' => $glasses->id,
            'status' => 'pending_donation',
        ]);

        $this->assertDatabaseHas('donation_requests', [
            'glasses_id' => $glasses->id,
            'conversation_id' => $conversation->id,
            'status' => 'pending',
        ]);

        $this->assertDatabaseHas('delivery_confirmations', [
            'glasses_id' => $glasses->id,
            'status' => 'pending',
        ]);

        $this->assertDatabaseHas('conversations', [
            'id' => $conversation->id,
            'status' => 'closed',
        ]);

        Notification::assertSentTo($recipient, RecipientMustConfirmDeliveryNotification::class);
        Notification::assertSentTo($admin, AdminNewDonationRequestNotification::class);
    }

    #[Test]
    public function marking_donated_skips_admin_approval_when_setting_is_disabled(): void
    {
        Notification::fake();
        app(SettingService::class)->set('donations.require_admin_approval_for_donated', false, 'bool');
        $admin = User::factory()->admin()->create();

        $donor = $this->donor();
        [$glasses, , $conversation, $recipient] = $this->openConversationFor($donor);

        $this->actingAs($donor)->post(
            route('donor.glasses.mark_donated', $glasses),
            [
                'conversation_id' => $conversation->id,
                'delivered_date' => now()->toDateString(),
            ]
        )->assertRedirect(route('donor.chats.index', ['conversation' => $conversation->id]));

        $this->assertDatabaseHas('glasses', [
            'id' => $glasses->id,
            'status' => 'donated',
        ]);

        $this->assertDatabaseHas('donation_requests', [
            'glasses_id' => $glasses->id,
            'status' => 'approved',
        ]);

        Notification::assertSentTo($recipient, RecipientMustConfirmDeliveryNotification::class);
        Notification::assertNotSentTo($admin, AdminNewDonationRequestNotification::class);
    }

    #[Test]
    public function marking_donated_requires_conversation_id_and_delivered_date(): void
    {
        $donor = $this->donor();
        [$glasses] = $this->openConversationFor($donor);

        $this->actingAs($donor)
            ->post(route('donor.glasses.mark_donated', $glasses), [])
            ->assertSessionHasErrors(['conversation_id', 'delivered_date']);
    }

    #[Test]
    public function donor_cannot_mark_donated_for_glasses_they_do_not_own(): void
    {
        $donor = $this->donor();
        $otherDonor = $this->donor();
        [$glasses, , $conversation] = $this->openConversationFor($otherDonor);

        $this->actingAs($donor)
            ->post(route('donor.glasses.mark_donated', $glasses), [
                'conversation_id' => $conversation->id,
                'delivered_date' => now()->toDateString(),
            ])
            ->assertForbidden();
    }

    #[Test]
    public function marking_donated_fails_when_no_matching_open_conversation_exists(): void
    {
        $donor = $this->donor();
        $glasses = Glasses::factory()->create(['user_id' => $donor->id, 'status' => 'reserved']);

        // conversation belongs to a different glasses / donor combo, so it won't match.
        $unrelatedConversation = Conversation::factory()->create();

        $this->actingAs($donor)
            ->post(route('donor.glasses.mark_donated', $glasses), [
                'conversation_id' => $unrelatedConversation->id,
                'delivered_date' => now()->toDateString(),
            ])
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertDatabaseMissing('donation_requests', [
            'glasses_id' => $glasses->id,
        ]);
    }
}
