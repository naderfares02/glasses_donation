<?php

namespace Tests\Feature\Chats;

use App\Events\MessageSent;
use App\Livewire\Chats\Inbox;
use App\Models\Conversation;
use App\Models\Glasses;
use App\Models\Message;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class InboxTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function donor_only_sees_their_own_conversations(): void
    {
        $donor = User::factory()->donor()->create();

        $ownConversation = Conversation::factory()->create(['donor_id' => $donor->id]);
        $otherConversation = Conversation::factory()->create();

        Livewire::actingAs($donor)
            ->test(Inbox::class)
            ->assertViewHas('conversations', function ($conversations) use ($ownConversation, $otherConversation) {
                return $conversations->contains('id', $ownConversation->id)
                    && ! $conversations->contains('id', $otherConversation->id);
            });
    }

    #[Test]
    public function recipient_only_sees_their_own_conversations(): void
    {
        $recipient = User::factory()->recipient()->create();

        $ownConversation = Conversation::factory()->create(['recipient_id' => $recipient->id]);
        $otherConversation = Conversation::factory()->create();

        Livewire::actingAs($recipient)
            ->test(Inbox::class)
            ->assertViewHas('conversations', function ($conversations) use ($ownConversation, $otherConversation) {
                return $conversations->contains('id', $ownConversation->id)
                    && ! $conversations->contains('id', $otherConversation->id);
            });
    }

    #[Test]
    public function a_role_other_than_donor_or_recipient_is_forbidden(): void
    {
        $admin = User::factory()->admin()->create();

        Livewire::actingAs($admin)
            ->test(Inbox::class)
            ->assertForbidden();
    }

    #[Test]
    public function mount_automatically_selects_the_first_conversation(): void
    {
        $donor = User::factory()->donor()->create();
        $conversation = Conversation::factory()->create(['donor_id' => $donor->id]);

        Livewire::actingAs($donor)
            ->test(Inbox::class)
            ->assertSet('activeConversationId', $conversation->id);
    }

    #[Test]
    public function a_participant_can_set_a_conversation_active_and_it_marks_the_other_partys_messages_as_read(): void
    {
        $donor = User::factory()->donor()->create();
        $recipient = User::factory()->recipient()->create();

        $conversation = Conversation::factory()->create([
            'donor_id' => $donor->id,
            'recipient_id' => $recipient->id,
        ]);

        $unreadFromRecipient = Message::factory()->create([
            'conversation_id' => $conversation->id,
            'sender_id' => $recipient->id,
            'read_at' => null,
        ]);

        Livewire::actingAs($donor)
            ->test(Inbox::class)
            ->call('setActive', $conversation->id)
            ->assertSet('activeConversationId', $conversation->id);

        $this->assertNotNull($unreadFromRecipient->fresh()->read_at);
    }

    #[Test]
    public function setActive_does_not_mark_the_users_own_messages_as_read(): void
    {
        $donor = User::factory()->donor()->create();
        $conversation = Conversation::factory()->create(['donor_id' => $donor->id]);

        $ownMessage = Message::factory()->create([
            'conversation_id' => $conversation->id,
            'sender_id' => $donor->id,
            'read_at' => null,
        ]);

        Livewire::actingAs($donor)
            ->test(Inbox::class)
            ->call('setActive', $conversation->id);

        $this->assertNull($ownMessage->fresh()->read_at);
    }

    #[Test]
    public function a_user_who_is_not_part_of_the_conversation_cannot_set_it_active(): void
    {
        $donor = User::factory()->donor()->create();
        $conversation = Conversation::factory()->create(); // طرفان مختلفان تماماً

        Livewire::actingAs($donor)
            ->test(Inbox::class)
            ->call('setActive', $conversation->id)
            ->assertForbidden();
    }

    #[Test]
    public function a_participant_can_send_a_message(): void
    {
        Event::fake([MessageSent::class]);

        $donor = User::factory()->donor()->create();
        $conversation = Conversation::factory()->create(['donor_id' => $donor->id, 'status' => 'open']);

        Livewire::actingAs($donor)
            ->test(Inbox::class)
            ->set('activeConversationId', $conversation->id)
            ->set('body', 'مرحباً، هل النظارة ما زالت متوفرة؟')
            ->call('send')
            ->assertSet('body', '')
            ->assertDispatched('clear-chat-box');

        $this->assertDatabaseHas('messages', [
            'conversation_id' => $conversation->id,
            'sender_id' => $donor->id,
            'body' => 'مرحباً، هل النظارة ما زالت متوفرة؟',
        ]);

        Event::assertDispatched(MessageSent::class);
    }

    #[Test]
    public function sending_an_empty_message_fails_validation(): void
    {
        $donor = User::factory()->donor()->create();
        $conversation = Conversation::factory()->create(['donor_id' => $donor->id, 'status' => 'open']);

        Livewire::actingAs($donor)
            ->test(Inbox::class)
            ->set('activeConversationId', $conversation->id)
            ->set('body', '')
            ->call('send')
            ->assertHasErrors(['body' => 'required']);

        $this->assertDatabaseCount('messages', 0);
    }

    #[Test]
    public function sending_a_message_longer_than_2000_characters_fails_validation(): void
    {
        $donor = User::factory()->donor()->create();
        $conversation = Conversation::factory()->create(['donor_id' => $donor->id, 'status' => 'open']);

        Livewire::actingAs($donor)
            ->test(Inbox::class)
            ->set('activeConversationId', $conversation->id)
            ->set('body', str_repeat('a', 2001))
            ->call('send')
            ->assertHasErrors(['body' => 'max']);
    }

    #[Test]
    public function a_message_cannot_be_sent_to_a_closed_conversation(): void
    {
        Event::fake([MessageSent::class]);

        $donor = User::factory()->donor()->create();
        $conversation = Conversation::factory()->closed()->create(['donor_id' => $donor->id]);

        Livewire::actingAs($donor)
            ->test(Inbox::class)
            ->set('activeConversationId', $conversation->id)
            ->set('body', 'رسالة بعد الإغلاق')
            ->call('send')
            ->assertHasErrors(['body']);

        $this->assertDatabaseCount('messages', 0);
        Event::assertNotDispatched(MessageSent::class);
    }

    #[Test]
    public function a_user_who_is_not_part_of_the_conversation_cannot_send_a_message(): void
    {
        $donor = User::factory()->donor()->create();
        $conversation = Conversation::factory()->create(['status' => 'open']); // طرفان مختلفان

        Livewire::actingAs($donor)
            ->test(Inbox::class)
            ->set('activeConversationId', $conversation->id)
            ->set('body', 'محاولة تطفل')
            ->call('send')
            ->assertForbidden();

        $this->assertDatabaseCount('messages', 0);
    }

    #[Test]
    public function when_the_donor_sends_a_message_a_reserved_glasses_item_moves_to_in_contact(): void
    {
        Event::fake([MessageSent::class]);

        $donor = User::factory()->donor()->create();
        $glasses = Glasses::factory()->reserved()->create(['user_id' => $donor->id]);
        $conversation = Conversation::factory()->create([
            'donor_id' => $donor->id,
            'glasses_id' => $glasses->id,
            'status' => 'open',
        ]);

        Livewire::actingAs($donor)
            ->test(Inbox::class)
            ->set('activeConversationId', $conversation->id)
            ->set('body', 'تم حجز النظارة، بنتواصل عالتفاصيل')
            ->call('send');

        $this->assertSame('in_contact', $glasses->fresh()->status);
    }

    #[Test]
    public function when_the_recipient_sends_a_message_the_glasses_status_is_not_changed(): void
    {
        Event::fake([MessageSent::class]);

        $recipient = User::factory()->recipient()->create();
        $donor = User::factory()->donor()->create();
        $glasses = Glasses::factory()->reserved()->create(['user_id' => $donor->id]);
        $conversation = Conversation::factory()->create([
            'donor_id' => $donor->id,
            'recipient_id' => $recipient->id,
            'glasses_id' => $glasses->id,
            'status' => 'open',
        ]);

        Livewire::actingAs($recipient)
            ->test(Inbox::class)
            ->set('activeConversationId', $conversation->id)
            ->set('body', 'تمام، بستنى التوصيل')
            ->call('send');

        $this->assertSame('reserved', $glasses->fresh()->status);
    }

    #[Test]
    public function sending_with_no_active_conversation_selected_does_nothing(): void
    {
        $donor = User::factory()->donor()->create();

        Livewire::actingAs($donor)
            ->test(Inbox::class)
            ->set('activeConversationId', null)
            ->set('body', 'test')
            ->call('send');

        $this->assertDatabaseCount('messages', 0);
    }
}
