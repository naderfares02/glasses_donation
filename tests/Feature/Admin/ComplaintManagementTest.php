<?php

namespace Tests\Feature\Admin;

use App\Models\Complaint;
use App\Models\User;
use App\Notifications\ComplaintReplyFromAdminNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ComplaintManagementTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function admin_can_view_complaints_index(): void
    {
        $admin = User::factory()->admin()->create();
        Complaint::factory()->count(3)->create();

        $this->actingAs($admin)
            ->get(route('admin.complaints.index'))
            ->assertOk();
    }

    #[Test]
    public function non_admin_cannot_view_complaints_index(): void
    {
        $donor = User::factory()->donor()->create();

        $this->actingAs($donor)
            ->get(route('admin.complaints.index'))
            ->assertForbidden();
    }

    #[Test]
    public function admin_can_view_a_single_complaint(): void
    {
        $admin = User::factory()->admin()->create();
        $complaint = Complaint::factory()->create();

        $this->actingAs($admin)
            ->get(route('admin.complaints.show', $complaint))
            ->assertOk();
    }

    #[Test]
    public function first_admin_to_reply_takes_ownership_of_the_complaint(): void
    {
        $admin = User::factory()->admin()->create();
        $complaint = Complaint::factory()->create(['status' => 'open', 'handled_by' => null]);

        $this->actingAs($admin)
            ->post(route('admin.complaints.reply', $complaint), [
                'body' => 'شكرًا لتواصلك، جاري المراجعة',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('complaints', [
            'id' => $complaint->id,
            'handled_by' => $admin->id,
            'status' => 'reviewing',
        ]);

        $this->assertDatabaseHas('complaint_messages', [
            'complaint_id' => $complaint->id,
            'sender_id' => $admin->id,
            'sender_role' => 'admin',
        ]);
    }

    #[Test]
    public function another_admin_cannot_reply_to_an_already_handled_complaint(): void
    {
        $firstAdmin = User::factory()->admin()->create();
        $secondAdmin = User::factory()->admin()->create();

        $complaint = Complaint::factory()->create([
            'status' => 'reviewing',
            'handled_by' => $firstAdmin->id,
        ]);

        $this->actingAs($secondAdmin)
            ->post(route('admin.complaints.reply', $complaint), [
                'body' => 'أنا كمان هرد',
            ])
            ->assertRedirect();

        $this->assertEquals(0, \App\Models\ComplaintMessage::where('complaint_id', $complaint->id)->count());
    }

    #[Test]
    public function super_admin_can_reply_even_if_another_admin_is_handling_it(): void
    {
        $admin = User::factory()->admin()->create();
        $superAdmin = User::factory()->superAdmin()->create();

        $complaint = Complaint::factory()->create([
            'status' => 'reviewing',
            'handled_by' => $admin->id,
        ]);

        $this->actingAs($superAdmin)
            ->post(route('admin.complaints.reply', $complaint), [
                'body' => 'تدخل من السوبر أدمن',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('complaint_messages', [
            'complaint_id' => $complaint->id,
            'sender_id' => $superAdmin->id,
        ]);
    }

    #[Test]
    public function admin_can_change_complaint_status(): void
    {
        $admin = User::factory()->admin()->create();
        $complaint = Complaint::factory()->create(['status' => 'open']);

        $this->actingAs($admin)
            ->post(route('admin.complaints.setStatus', $complaint), [
                'status' => 'dismissed',
                'resolution_note' => 'لا يوجد خرق فعلي',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('complaints', [
            'id' => $complaint->id,
            'status' => 'dismissed',
            'handled_by' => $admin->id,
            'resolution_note' => 'لا يوجد خرق فعلي',
        ]);
    }

    #[Test]
    public function admin_can_close_a_complaint(): void
    {
        $admin = User::factory()->admin()->create();
        $complaint = Complaint::factory()->create(['status' => 'reviewing']);

        $this->actingAs($admin)
            ->post(route('admin.complaints.close', $complaint))
            ->assertRedirect();

        $this->assertDatabaseHas('complaints', [
            'id' => $complaint->id,
            'status' => 'resolved',
        ]);
    }

    #[Test]
    public function replying_to_a_complaint_notifies_the_reporter(): void
    {
        Notification::fake();

        $admin = User::factory()->admin()->create();
        $reporter = User::factory()->donor()->create();
        $complaint = Complaint::factory()->create([
            'status' => 'open',
            'handled_by' => null,
            'reporter_id' => $reporter->id,
        ]);

        $this->actingAs($admin)
            ->post(route('admin.complaints.reply', $complaint), [
                'body' => 'شكرًا لتواصلك، جاري المراجعة',
            ]);

        Notification::assertSentTo($reporter, ComplaintReplyFromAdminNotification::class);
    }
}
