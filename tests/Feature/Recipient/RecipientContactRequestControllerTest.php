<?php

namespace Tests\Feature\Recipient;

use App\Models\ContactRequest;
use App\Models\DonationReceipt;
use App\Models\Glasses;
use App\Models\User;
use App\Notifications\NewContactRequestNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class RecipientContactRequestControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function recipient(): User
    {
        return User::factory()->recipient()->create();
    }

    /*
    |--------------------------------------------------------------------------
    | store()
    |--------------------------------------------------------------------------
    */

    #[Test]
    public function recipient_cannot_request_glasses_that_are_not_available(): void
    {
        $recipient = $this->recipient();
        $glasses = Glasses::factory()->create(['status' => 'reserved']);

        $this->actingAs($recipient)
            ->post(route('recipient.contact-requests.store', $glasses))
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertDatabaseMissing('contact_requests', [
            'glasses_id' => $glasses->id,
            'recipient_id' => $recipient->id,
        ]);
    }

    #[Test]
    public function sending_a_contact_request_notifies_the_donor(): void
    {
        Notification::fake();

        $donor = User::factory()->donor()->create();
        $recipient = $this->recipient();
        $glasses = Glasses::factory()->available()->create(['user_id' => $donor->id]);

        $this->actingAs($recipient)
            ->post(route('recipient.contact-requests.store', $glasses));

        Notification::assertSentTo($donor, NewContactRequestNotification::class);
    }

    #[Test]
    public function recipient_cannot_request_the_same_glasses_again_after_being_rejected(): void
    {
        $donor = User::factory()->donor()->create();
        $recipient = $this->recipient();
        $glasses = Glasses::factory()->available()->create(['user_id' => $donor->id]);

        ContactRequest::factory()->rejected()->create([
            'glasses_id' => $glasses->id,
            'donor_id' => $donor->id,
            'recipient_id' => $recipient->id,
        ]);

        $this->actingAs($recipient)
            ->post(route('recipient.contact-requests.store', $glasses))
            ->assertSessionHas('error');

        $this->assertEquals(
            1,
            ContactRequest::where('glasses_id', $glasses->id)
                ->where('recipient_id', $recipient->id)
                ->count()
        );
    }

    /*
    |--------------------------------------------------------------------------
    | index()
    |--------------------------------------------------------------------------
    */

    #[Test]
    public function recipient_can_view_their_own_contact_requests(): void
    {
        $recipient = $this->recipient();
        $otherRecipient = $this->recipient();

        $mine = ContactRequest::factory()->create(['recipient_id' => $recipient->id]);
        ContactRequest::factory()->create(['recipient_id' => $otherRecipient->id]);

        $response = $this->actingAs($recipient)->get(route('recipient.contact-requests.index'));

        $response->assertOk()->assertViewIs('recipient.glasses_requests');

        $ids = $response->viewData('requests')->pluck('id');

        $this->assertTrue($ids->contains($mine->id));
        $this->assertCount(1, $ids);
    }

    #[Test]
    public function index_exposes_which_glasses_have_already_been_donated_to_the_recipient(): void
    {
        $recipient = $this->recipient();

        $receipt = DonationReceipt::factory()->create(['recipient_id' => $recipient->id]);
        $unrelatedReceipt = DonationReceipt::factory()->create();

        $response = $this->actingAs($recipient)->get(route('recipient.contact-requests.index'));

        $donatedIds = $response->viewData('donatedGlassesIds');

        $this->assertContains($receipt->glasses_id, $donatedIds);
        $this->assertNotContains($unrelatedReceipt->glasses_id, $donatedIds);
    }

    /*
    |--------------------------------------------------------------------------
    | withdraw()
    |--------------------------------------------------------------------------
    */

    #[Test]
    public function recipient_can_withdraw_their_own_pending_request(): void
    {
        $recipient = $this->recipient();
        $request = ContactRequest::factory()->create([
            'recipient_id' => $recipient->id,
            'status' => 'pending',
        ]);

        $this->actingAs($recipient)
            ->patch(route('recipient.requests.withdraw', $request))
            ->assertRedirect();

        $this->assertDatabaseMissing('contact_requests', ['id' => $request->id]);
    }

    #[Test]
    public function recipient_cannot_withdraw_a_request_that_is_no_longer_pending(): void
    {
        $recipient = $this->recipient();
        $request = ContactRequest::factory()->accepted()->create([
            'recipient_id' => $recipient->id,
        ]);

        $this->actingAs($recipient)
            ->patch(route('recipient.requests.withdraw', $request))
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertDatabaseHas('contact_requests', ['id' => $request->id]);
    }

    #[Test]
    public function recipient_cannot_withdraw_someone_elses_request(): void
    {
        $recipient = $this->recipient();
        $otherRecipient = $this->recipient();
        $request = ContactRequest::factory()->create([
            'recipient_id' => $otherRecipient->id,
            'status' => 'pending',
        ]);

        $this->actingAs($recipient)
            ->patch(route('recipient.requests.withdraw', $request))
            ->assertForbidden();

        $this->assertDatabaseHas('contact_requests', ['id' => $request->id]);
    }
}
