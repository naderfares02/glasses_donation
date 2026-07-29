<?php

namespace Tests\Feature\Complaints;

use App\Models\Complaint;
use App\Models\ComplaintMessage;
use App\Models\Conversation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ComplaintFlowTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function a_party_in_the_conversation_can_file_a_complaint(): void
    {
        Notification::fake();

        $donor = User::factory()->donor()->create();
        $recipient = User::factory()->recipient()->create();
        $admin = User::factory()->admin()->create();

        $conversation = Conversation::factory()->create([
            'donor_id' => $donor->id,
            'recipient_id' => $recipient->id,
        ]);

        $this->actingAs($donor)
            ->post(route('complaints.store', $conversation), [
                'reason' => 'no_response',
                'description' => 'المستفيد ما بيردش',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('complaints', [
            'conversation_id' => $conversation->id,
            'reporter_id' => $donor->id,
            'reported_user_id' => $recipient->id,
            'reason' => 'no_response',
            'status' => 'open',
        ]);

        Notification::assertSentTo($admin, \App\Notifications\ComplaintCreatedNotification::class);
    }

    #[Test]
    public function someone_outside_the_conversation_cannot_file_a_complaint(): void
    {
        $donor = User::factory()->donor()->create();
        $recipient = User::factory()->recipient()->create();
        $stranger = User::factory()->donor()->create();

        $conversation = Conversation::factory()->create([
            'donor_id' => $donor->id,
            'recipient_id' => $recipient->id,
        ]);

        $this->actingAs($stranger)
            ->post(route('complaints.store', $conversation), [
                'reason' => 'spam',
            ])
            ->assertForbidden();
    }

    #[Test]
    public function reporter_can_view_their_own_complaint(): void
    {
        $donor = User::factory()->donor()->create();
        $complaint = Complaint::factory()->create(['reporter_id' => $donor->id]);

        $this->actingAs($donor)
            ->get(route('complaints.show', $complaint))
            ->assertOk();
    }

    #[Test]
    public function someone_else_cannot_view_a_complaint_they_did_not_file(): void
    {
        $donor = User::factory()->donor()->create();
        $complaint = Complaint::factory()->create();

        $this->actingAs($donor)
            ->get(route('complaints.show', $complaint))
            ->assertForbidden();
    }

    #[Test]
    public function reporter_can_send_a_follow_up_message_after_admin_reply(): void
    {
        $donor = User::factory()->donor()->create();
        $admin = User::factory()->admin()->create();

        $complaint = Complaint::factory()->create(['reporter_id' => $donor->id]);
        ComplaintMessage::factory()->fromAdmin()->create([
            'complaint_id' => $complaint->id,
            'sender_id' => $admin->id,
        ]);

        $this->actingAs($donor)
            ->post(route('complaints.message', $complaint), [
                'body' => 'شكرا، بنتظر التحديث',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('complaint_messages', [
            'complaint_id' => $complaint->id,
            'sender_id' => $donor->id,
            'sender_role' => 'user',
            'body' => 'شكرا، بنتظر التحديث',
        ]);
    }

    #[Test]
    public function reporter_cannot_send_two_messages_in_a_row_without_admin_reply(): void
    {
        $donor = User::factory()->donor()->create();
        $complaint = Complaint::factory()->create(['reporter_id' => $donor->id]);

        ComplaintMessage::factory()->create([
            'complaint_id' => $complaint->id,
            'sender_id' => $donor->id,
            'sender_role' => 'user',
        ]);

        $this->actingAs($donor)
            ->post(route('complaints.message', $complaint), [
                'body' => 'رسالة تانية بسرعة',
            ]);

        $this->assertEquals(
            1,
            ComplaintMessage::where('complaint_id', $complaint->id)->count()
        );
    }

    #[Test]
    public function reporter_can_close_their_own_complaint(): void
    {
        $donor = User::factory()->donor()->create();
        $complaint = Complaint::factory()->create([
            'reporter_id' => $donor->id,
            'status' => 'open',
        ]);

        $this->actingAs($donor)
            ->post(route('complaints.close', $complaint))
            ->assertRedirect();

        $this->assertDatabaseHas('complaints', [
            'id' => $complaint->id,
            'status' => 'resolved',
        ]);
    }

    #[Test]
    public function guest_cannot_close_a_complaint(): void
    {
        $complaint = Complaint::factory()->create();

        $this->post(route('complaints.close', $complaint))
            ->assertRedirect(route('login'));
    }
}
