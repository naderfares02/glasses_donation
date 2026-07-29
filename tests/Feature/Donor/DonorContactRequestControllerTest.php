<?php

namespace Tests\Feature\Donor;

use App\Models\ContactRequest;
use App\Models\Conversation;
use App\Models\Glasses;
use App\Models\User;
use App\Notifications\ContactRequestAcceptedNotification;
use App\Notifications\ContactRequestRejectedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DonorContactRequestControllerTest extends TestCase
{
    use RefreshDatabase;

    // ------------------------------------------------------------------
    // index()
    // ------------------------------------------------------------------

    #[Test]
    public function donor_can_view_requests_for_their_own_glasses(): void
    {
        $donor = User::factory()->donor()->create();
        $glasses = Glasses::factory()->create(['user_id' => $donor->id]);
        ContactRequest::factory()->create(['glasses_id' => $glasses->id, 'donor_id' => $donor->id]);

        $this->actingAs($donor)
            ->get(route('donor.requests.index', $glasses))
            ->assertOk()
            ->assertViewIs('donor.requests.index')
            ->assertViewHas('requests', fn ($requests) => $requests->count() === 1);
    }

    #[Test]
    public function donor_cannot_view_requests_for_someone_elses_glasses(): void
    {
        $donor = User::factory()->donor()->create();
        $glasses = Glasses::factory()->create(); // مالك مختلف

        $this->actingAs($donor)
            ->get(route('donor.requests.index', $glasses))
            ->assertForbidden();
    }

    // ------------------------------------------------------------------
    // accept()
    // ------------------------------------------------------------------

    #[Test]
    public function donor_can_accept_a_pending_request(): void
    {
        Notification::fake();

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
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('contact_requests', [
            'id' => $request->id,
            'status' => 'accepted',
        ]);

        $this->assertDatabaseHas('glasses', [
            'id' => $glasses->id,
            'status' => 'reserved',
            'active_contact_request_id' => $request->id,
        ]);

        $this->assertDatabaseHas('conversations', [
            'contact_request_id' => $request->id,
            'donor_id' => $donor->id,
            'recipient_id' => $recipient->id,
            'status' => 'open',
        ]);

        Notification::assertSentTo($recipient, ContactRequestAcceptedNotification::class);
    }

    #[Test]
    public function accepting_a_request_puts_other_pending_requests_for_the_same_glasses_on_hold(): void
    {
        Notification::fake();

        $donor = User::factory()->donor()->create();
        $glasses = Glasses::factory()->available()->create(['user_id' => $donor->id]);

        $winner = ContactRequest::factory()->create([
            'glasses_id' => $glasses->id,
            'donor_id' => $donor->id,
            'status' => 'pending',
        ]);
        $loser = ContactRequest::factory()->create([
            'glasses_id' => $glasses->id,
            'donor_id' => $donor->id,
            'status' => 'pending',
        ]);

        $this->actingAs($donor)->post(route('donor.requests.accept', $winner));

        $this->assertDatabaseHas('contact_requests', ['id' => $winner->id, 'status' => 'accepted']);
        $this->assertDatabaseHas('contact_requests', ['id' => $loser->id, 'status' => 'on_hold']);
    }

    #[Test]
    public function accept_reuses_an_existing_conversation_for_the_same_contact_request_instead_of_duplicating_it(): void
    {
        Notification::fake();

        $donor = User::factory()->donor()->create();
        $glasses = Glasses::factory()->available()->create(['user_id' => $donor->id]);
        $request = ContactRequest::factory()->create([
            'glasses_id' => $glasses->id,
            'donor_id' => $donor->id,
            'status' => 'pending',
        ]);

        // محادثة قديمة موجودة مسبقاً لنفس contact_request (حالة نادرة لكن ممكنة)
        Conversation::factory()->create([
            'contact_request_id' => $request->id,
            'glasses_id' => $glasses->id,
            'donor_id' => $donor->id,
            'recipient_id' => $request->recipient_id,
        ]);

        $this->actingAs($donor)->post(route('donor.requests.accept', $request));

        $this->assertDatabaseCount('conversations', 1);
    }

    #[Test]
    public function donor_cannot_accept_a_request_for_someone_elses_glasses(): void
    {
        $donor = User::factory()->donor()->create();
        $request = ContactRequest::factory()->create(['status' => 'pending']); // متبرع مختلف

        $this->actingAs($donor)
            ->post(route('donor.requests.accept', $request))
            ->assertForbidden();

        $this->assertDatabaseHas('contact_requests', ['id' => $request->id, 'status' => 'pending']);
    }

    #[Test]
    public function accepting_an_already_accepted_request_does_nothing_and_shows_an_error(): void
    {
        $donor = User::factory()->donor()->create();
        $glasses = Glasses::factory()->reserved()->create(['user_id' => $donor->id]);
        $request = ContactRequest::factory()->accepted()->create([
            'glasses_id' => $glasses->id,
            'donor_id' => $donor->id,
        ]);

        $this->actingAs($donor)
            ->post(route('donor.requests.accept', $request))
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertDatabaseCount('conversations', 0);
    }

    #[Test]
    public function accepting_a_rejected_request_does_nothing_and_shows_an_error(): void
    {
        $donor = User::factory()->donor()->create();
        $glasses = Glasses::factory()->available()->create(['user_id' => $donor->id]);
        $request = ContactRequest::factory()->rejected()->create([
            'glasses_id' => $glasses->id,
            'donor_id' => $donor->id,
        ]);

        $this->actingAs($donor)
            ->post(route('donor.requests.accept', $request))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('contact_requests', ['id' => $request->id, 'status' => 'rejected']);
    }

    #[Test]
    public function accepting_fails_with_a_conflict_when_the_glasses_is_no_longer_available(): void
    {
        $donor = User::factory()->donor()->create();
        // النظارة انحجزت أو انتوهبت من طلب تاني بالتوازي
        $glasses = Glasses::factory()->donated()->create(['user_id' => $donor->id]);
        $request = ContactRequest::factory()->create([
            'glasses_id' => $glasses->id,
            'donor_id' => $donor->id,
            'status' => 'pending',
        ]);

        $this->actingAs($donor)
            ->post(route('donor.requests.accept', $request))
            ->assertStatus(409);

        $this->assertDatabaseHas('contact_requests', ['id' => $request->id, 'status' => 'pending']);
    }

    #[Test]
    public function accepting_fails_with_a_conflict_when_the_glasses_already_has_an_active_contact_request(): void
    {
        $donor = User::factory()->donor()->create();
        $existingActive = ContactRequest::factory()->create(['donor_id' => $donor->id, 'status' => 'accepted']);
        $glasses = Glasses::factory()->available()->create([
            'user_id' => $donor->id,
            'active_contact_request_id' => $existingActive->id,
        ]);
        $newRequest = ContactRequest::factory()->create([
            'glasses_id' => $glasses->id,
            'donor_id' => $donor->id,
            'status' => 'pending',
        ]);

        $this->actingAs($donor)
            ->post(route('donor.requests.accept', $newRequest))
            ->assertStatus(409);

        $this->assertDatabaseHas('contact_requests', ['id' => $newRequest->id, 'status' => 'pending']);
    }

    // ------------------------------------------------------------------
    // reject()
    // ------------------------------------------------------------------

    #[Test]
    public function donor_can_reject_a_pending_request(): void
    {
        Notification::fake();

        $donor = User::factory()->donor()->create();
        $recipient = User::factory()->recipient()->create();
        $request = ContactRequest::factory()->create([
            'donor_id' => $donor->id,
            'recipient_id' => $recipient->id,
            'status' => 'pending',
        ]);

        $this->actingAs($donor)
            ->post(route('donor.requests.reject', $request))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('contact_requests', ['id' => $request->id, 'status' => 'rejected']);

        Notification::assertSentTo($recipient, ContactRequestRejectedNotification::class);
    }

    #[Test]
    public function donor_cannot_reject_someone_elses_request(): void
    {
        $donor = User::factory()->donor()->create();
        $request = ContactRequest::factory()->create(['status' => 'pending']); // متبرع مختلف

        $this->actingAs($donor)
            ->post(route('donor.requests.reject', $request))
            ->assertForbidden();

        $this->assertDatabaseHas('contact_requests', ['id' => $request->id, 'status' => 'pending']);
    }
}
