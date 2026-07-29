<?php

namespace Tests\Feature\Admin;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ChatControllerTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function admin_can_view_a_conversation(): void
    {
        $admin = User::factory()->admin()->create();
        $conversation = Conversation::factory()->create();
        Message::factory()->create(['conversation_id' => $conversation->id]);

        $this->actingAs($admin)
            ->get(route('admin.conversations.show', $conversation))
            ->assertOk()
            ->assertViewIs('admin.chats.show')
            ->assertViewHas('conversation')
            ->assertViewHas('messages');
    }

    #[Test]
    public function super_admin_can_view_a_conversation(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $conversation = Conversation::factory()->create();

        $this->actingAs($superAdmin)
            ->get(route('admin.conversations.show', $conversation))
            ->assertOk();
    }

    #[Test]
    public function donor_cannot_view_a_conversation_via_the_admin_route(): void
    {
        $donor = User::factory()->donor()->create();
        $conversation = Conversation::factory()->create();

        $this->actingAs($donor)
            ->get(route('admin.conversations.show', $conversation))
            ->assertForbidden();
    }

    #[Test]
    public function recipient_cannot_view_a_conversation_via_the_admin_route(): void
    {
        $recipient = User::factory()->recipient()->create();
        $conversation = Conversation::factory()->create();

        $this->actingAs($recipient)
            ->get(route('admin.conversations.show', $conversation))
            ->assertForbidden();
    }

    #[Test]
    public function guest_is_redirected_to_login_when_viewing_a_conversation(): void
    {
        $conversation = Conversation::factory()->create();

        $this->get(route('admin.conversations.show', $conversation))
            ->assertRedirect(route('login'));
    }

    #[Test]
    public function admin_can_toggle_an_open_conversation_to_closed(): void
    {
        $admin = User::factory()->admin()->create();
        $conversation = Conversation::factory()->create(['status' => 'open']);

        $this->actingAs($admin)
            ->post(route('admin.conversations.toggle', $conversation))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('conversations', [
            'id' => $conversation->id,
            'status' => 'closed',
        ]);
    }

    #[Test]
    public function admin_can_toggle_a_closed_conversation_back_to_open(): void
    {
        $admin = User::factory()->admin()->create();
        $conversation = Conversation::factory()->closed()->create();

        $this->actingAs($admin)
            ->post(route('admin.conversations.toggle', $conversation));

        $this->assertDatabaseHas('conversations', [
            'id' => $conversation->id,
            'status' => 'open',
        ]);
    }

    #[Test]
    public function donor_cannot_toggle_a_conversation_status(): void
    {
        $donor = User::factory()->donor()->create();
        $conversation = Conversation::factory()->create(['status' => 'open']);

        $this->actingAs($donor)
            ->post(route('admin.conversations.toggle', $conversation))
            ->assertForbidden();

        $this->assertDatabaseHas('conversations', [
            'id' => $conversation->id,
            'status' => 'open', // لم تتغير
        ]);
    }

    #[Test]
    public function recipient_cannot_toggle_a_conversation_status(): void
    {
        $recipient = User::factory()->recipient()->create();
        $conversation = Conversation::factory()->create(['status' => 'open']);

        $this->actingAs($recipient)
            ->post(route('admin.conversations.toggle', $conversation))
            ->assertForbidden();

        $this->assertDatabaseHas('conversations', [
            'id' => $conversation->id,
            'status' => 'open', // لم تتغير
        ]);
    }

    #[Test]
    public function guest_cannot_toggle_a_conversation_status(): void
    {
        $conversation = Conversation::factory()->create(['status' => 'open']);

        $this->post(route('admin.conversations.toggle', $conversation))
            ->assertRedirect(route('login'));

        $this->assertDatabaseHas('conversations', [
            'id' => $conversation->id,
            'status' => 'open',
        ]);
    }
}
