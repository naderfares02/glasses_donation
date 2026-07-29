<?php

namespace Tests\Feature\Admin;

use App\Models\Conversation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class UserManagementControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function admin(): User
    {
        return User::factory()->admin()->create();
    }

    protected function superAdmin(): User
    {
        return User::factory()->superAdmin()->create();
    }

    /*
    |--------------------------------------------------------------------------
    | index()
    |--------------------------------------------------------------------------
    */

    #[Test]
    public function admin_can_view_the_users_list(): void
    {
        $admin = $this->admin();
        User::factory()->count(3)->recipient()->create();

        $this->actingAs($admin)
            ->get(route('admin.users.index'))
            ->assertOk()
            ->assertViewIs('admin.users.index')
            ->assertViewHas('users')
            ->assertViewHas('counts');
    }

    #[Test]
    public function non_admin_cannot_view_the_users_list(): void
    {
        $donor = User::factory()->donor()->create();

        $this->actingAs($donor)
            ->get(route('admin.users.index'))
            ->assertForbidden();
    }

    #[Test]
    public function the_users_list_never_shows_the_authenticated_user_or_super_admins(): void
    {
        $superAdmin = $this->superAdmin();
        $otherSuperAdmin = $this->superAdmin();
        $donor = User::factory()->donor()->create();

        $response = $this->actingAs($superAdmin)->get(route('admin.users.index'));

        $ids = $response->viewData('users')->pluck('id');

        $this->assertFalse($ids->contains($superAdmin->id));
        $this->assertFalse($ids->contains($otherSuperAdmin->id));
        $this->assertTrue($ids->contains($donor->id));
    }

    #[Test]
    public function a_regular_admin_cannot_see_other_admins_in_the_list(): void
    {
        $admin = $this->admin();
        $otherAdmin = $this->admin();
        $donor = User::factory()->donor()->create();

        $response = $this->actingAs($admin)->get(route('admin.users.index'));

        $ids = $response->viewData('users')->pluck('id');

        $this->assertFalse($ids->contains($otherAdmin->id));
        $this->assertFalse($ids->contains($admin->id));
        $this->assertTrue($ids->contains($donor->id));
    }

    #[Test]
    public function it_searches_users_by_name_or_email(): void
    {
        $admin = $this->admin();
        $match = User::factory()->donor()->create(['name' => 'خالد الدونر', 'email' => 'khaled@example.com']);
        User::factory()->donor()->create(['name' => 'سامي', 'email' => 'sami@example.com']);

        $response = $this->actingAs($admin)->get(route('admin.users.index', ['q' => 'خالد']));

        $ids = $response->viewData('users')->pluck('id');

        $this->assertTrue($ids->contains($match->id));
        $this->assertCount(1, $ids);
    }

    #[Test]
    public function it_filters_users_by_role(): void
    {
        $admin = $this->admin();
        $donor = User::factory()->donor()->create();
        $recipient = User::factory()->recipient()->create();

        $response = $this->actingAs($admin)->get(route('admin.users.index', ['role' => 'donor']));

        $ids = $response->viewData('users')->pluck('id');

        $this->assertTrue($ids->contains($donor->id));
        $this->assertFalse($ids->contains($recipient->id));
    }

    #[Test]
    public function it_filters_users_by_status(): void
    {
        $admin = $this->admin();
        $suspended = User::factory()->donor()->suspended()->create();
        $active = User::factory()->donor()->create();

        $response = $this->actingAs($admin)->get(route('admin.users.index', ['status' => 'suspended']));

        $ids = $response->viewData('users')->pluck('id');

        $this->assertTrue($ids->contains($suspended->id));
        $this->assertFalse($ids->contains($active->id));
    }

    #[Test]
    public function it_can_filter_to_only_soft_deleted_users(): void
    {
        $admin = $this->admin();
        $deleted = User::factory()->donor()->create();
        $deleted->delete();
        $active = User::factory()->donor()->create();

        $response = $this->actingAs($admin)->get(route('admin.users.index', ['deleted' => '1']));

        $ids = $response->viewData('users')->pluck('id');

        $this->assertTrue($ids->contains($deleted->id));
        $this->assertFalse($ids->contains($active->id));
    }

    /*
    |--------------------------------------------------------------------------
    | show()
    |--------------------------------------------------------------------------
    */

    #[Test]
    public function admin_can_view_a_donor_profile_with_donor_stats(): void
    {
        $admin = $this->admin();
        $donor = User::factory()->donor()->create();

        $response = $this->actingAs($admin)->get(route('admin.users.show', $donor));

        $response->assertOk()->assertViewIs('admin.users.show');
        $stats = $response->viewData('stats');

        $this->assertArrayHasKey('glasses_posted', $stats);
        $this->assertArrayHasKey('donations_pending', $stats);
    }

    #[Test]
    public function admin_can_view_a_recipient_profile_with_recipient_stats(): void
    {
        $admin = $this->admin();
        $recipient = User::factory()->recipient()->create();

        $response = $this->actingAs($admin)->get(route('admin.users.show', $recipient));

        $response->assertOk();
        $stats = $response->viewData('stats');

        $this->assertArrayHasKey('donation_requests', $stats);
        $this->assertArrayHasKey('contact_requests', $stats);
    }

    /*
    |--------------------------------------------------------------------------
    | update()
    |--------------------------------------------------------------------------
    */

    #[Test]
    public function admin_can_update_basic_user_info(): void
    {
        $admin = $this->admin();
        $donor = User::factory()->donor()->create();

        $this->actingAs($admin)
            ->patch(route('admin.users.update', $donor), [
                'name' => 'اسم محدّث',
                'email' => 'updated@example.com',
                'phone' => '0599999999',
                'city' => 'نابلس',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('users', [
            'id' => $donor->id,
            'name' => 'اسم محدّث',
            'email' => 'updated@example.com',
        ]);
    }

    #[Test]
    public function updating_a_user_requires_all_mandatory_fields(): void
    {
        $admin = $this->admin();
        $donor = User::factory()->donor()->create();

        $this->actingAs($admin)
            ->patch(route('admin.users.update', $donor), [])
            ->assertSessionHasErrors(['name', 'email', 'phone', 'city']);
    }

    /*
    |--------------------------------------------------------------------------
    | suspend() / unsuspend()
    |--------------------------------------------------------------------------
    */

    #[Test]
    public function admin_can_suspend_a_donor(): void
    {
        $admin = $this->admin();
        $donor = User::factory()->donor()->create();

        $this->actingAs($admin)
            ->post(route('admin.users.suspend', $donor), ['reason' => 'مخالفة الشروط'])
            ->assertRedirect();

        $this->assertDatabaseHas('users', [
            'id' => $donor->id,
            'status' => 'suspended',
            'suspended_by' => $admin->id,
            'suspended_reason' => 'مخالفة الشروط',
        ]);
    }

    #[Test]
    public function suspend_requires_a_reason(): void
    {
        $admin = $this->admin();
        $donor = User::factory()->donor()->create();

        $this->actingAs($admin)
            ->post(route('admin.users.suspend', $donor), [])
            ->assertSessionHasErrors(['reason']);
    }

    #[Test]
    public function admin_cannot_suspend_themselves(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)
            ->post(route('admin.users.suspend', $admin), ['reason' => 'test'])
            ->assertForbidden();
    }

    #[Test]
    public function regular_admin_cannot_suspend_another_admin(): void
    {
        $admin = $this->admin();
        $otherAdmin = $this->admin();

        $this->actingAs($admin)
            ->post(route('admin.users.suspend', $otherAdmin), ['reason' => 'test'])
            ->assertForbidden();
    }

    #[Test]
    public function super_admin_can_suspend_an_admin(): void
    {
        $superAdmin = $this->superAdmin();
        $admin = $this->admin();

        $this->actingAs($superAdmin)
            ->post(route('admin.users.suspend', $admin), ['reason' => 'test'])
            ->assertRedirect();

        $this->assertDatabaseHas('users', [
            'id' => $admin->id,
            'status' => 'suspended',
        ]);
    }

    #[Test]
    public function admin_can_unsuspend_a_donor(): void
    {
        $admin = $this->admin();
        $donor = User::factory()->donor()->suspended()->create();

        $this->actingAs($admin)
            ->post(route('admin.users.unsuspend', $donor))
            ->assertRedirect();

        $this->assertDatabaseHas('users', [
            'id' => $donor->id,
            'status' => 'active',
            'suspended_at' => null,
            'suspended_reason' => null,
        ]);
    }

    #[Test]
    public function regular_admin_cannot_unsuspend_another_admin(): void
    {
        $admin = $this->admin();
        $otherAdmin = User::factory()->admin()->suspended()->create();

        $this->actingAs($admin)
            ->post(route('admin.users.unsuspend', $otherAdmin))
            ->assertForbidden();
    }

    /*
    |--------------------------------------------------------------------------
    | changeRole()
    |--------------------------------------------------------------------------
    */

    #[Test]
    public function super_admin_can_change_a_users_role(): void
    {
        $superAdmin = $this->superAdmin();
        $recipient = User::factory()->recipient()->create();

        $this->actingAs($superAdmin)
            ->post(route('admin.users.change_role', $recipient), ['role' => 'donor'])
            ->assertRedirect();

        $this->assertDatabaseHas('users', [
            'id' => $recipient->id,
            'role' => 'donor',
            'role_changed_by' => $superAdmin->id,
        ]);
    }

    #[Test]
    public function regular_admin_cannot_change_a_users_role(): void
    {
        $admin = $this->admin();
        $recipient = User::factory()->recipient()->create();

        $this->actingAs($admin)
            ->post(route('admin.users.change_role', $recipient), ['role' => 'donor'])
            ->assertForbidden();
    }

    #[Test]
    public function super_admin_cannot_change_their_own_role(): void
    {
        $superAdmin = $this->superAdmin();

        $this->actingAs($superAdmin)
            ->post(route('admin.users.change_role', $superAdmin), ['role' => 'donor'])
            ->assertForbidden();
    }

    #[Test]
    public function change_role_rejects_an_invalid_role_value(): void
    {
        $superAdmin = $this->superAdmin();
        $recipient = User::factory()->recipient()->create();

        $this->actingAs($superAdmin)
            ->post(route('admin.users.change_role', $recipient), ['role' => 'not-a-role'])
            ->assertSessionHasErrors(['role']);
    }

    /*
    |--------------------------------------------------------------------------
    | destroy() / restore()
    |--------------------------------------------------------------------------
    */

    #[Test]
    public function super_admin_can_soft_delete_a_user(): void
    {
        $superAdmin = $this->superAdmin();
        $donor = User::factory()->donor()->create();

        $this->actingAs($superAdmin)
            ->delete(route('admin.users.destroy', $donor))
            ->assertRedirect();

        $this->assertSoftDeleted('users', ['id' => $donor->id]);
    }

    #[Test]
    public function regular_admin_cannot_delete_a_user(): void
    {
        $admin = $this->admin();
        $donor = User::factory()->donor()->create();

        $this->actingAs($admin)
            ->delete(route('admin.users.destroy', $donor))
            ->assertForbidden();

        $this->assertDatabaseHas('users', ['id' => $donor->id, 'deleted_at' => null]);
    }

    #[Test]
    public function super_admin_cannot_delete_themselves(): void
    {
        $superAdmin = $this->superAdmin();

        $this->actingAs($superAdmin)
            ->delete(route('admin.users.destroy', $superAdmin))
            ->assertForbidden();
    }

    #[Test]
    public function super_admin_can_restore_a_soft_deleted_user(): void
    {
        $superAdmin = $this->superAdmin();
        $donor = User::factory()->donor()->create();
        $donor->delete();

        $this->actingAs($superAdmin)
            ->post(route('admin.users.restore', $donor->id))
            ->assertRedirect();

        $this->assertDatabaseHas('users', ['id' => $donor->id, 'deleted_at' => null]);
    }

    #[Test]
    public function regular_admin_cannot_restore_a_user(): void
    {
        $admin = $this->admin();
        $donor = User::factory()->donor()->create();
        $donor->delete();

        $this->actingAs($admin)
            ->post(route('admin.users.restore', $donor->id))
            ->assertForbidden();
    }

    /*
    |--------------------------------------------------------------------------
    | closeOpenConversations() / openClosedConversations()
    |--------------------------------------------------------------------------
    */

    #[Test]
    public function super_admin_can_close_all_open_conversations_for_a_user(): void
    {
        $superAdmin = $this->superAdmin();
        $donor = User::factory()->donor()->create();

        $open1 = Conversation::factory()->create(['donor_id' => $donor->id, 'status' => 'open']);
        $open2 = Conversation::factory()->create(['recipient_id' => $donor->id, 'status' => 'open']);
        $unrelated = Conversation::factory()->create(['status' => 'open']);

        $this->actingAs($superAdmin)
            ->post(route('admin.users.close_conversations', $donor))
            ->assertRedirect();

        $this->assertDatabaseHas('conversations', ['id' => $open1->id, 'status' => 'closed']);
        $this->assertDatabaseHas('conversations', ['id' => $open2->id, 'status' => 'closed']);
        $this->assertDatabaseHas('conversations', ['id' => $unrelated->id, 'status' => 'open']);
    }

    #[Test]
    public function regular_admin_cannot_close_a_users_conversations(): void
    {
        $admin = $this->admin();
        $donor = User::factory()->donor()->create();

        $this->actingAs($admin)
            ->post(route('admin.users.close_conversations', $donor))
            ->assertForbidden();
    }

    #[Test]
    public function super_admin_can_reopen_all_closed_conversations_for_a_user(): void
    {
        $superAdmin = $this->superAdmin();
        $donor = User::factory()->donor()->create();

        $closed = Conversation::factory()->create(['donor_id' => $donor->id, 'status' => 'closed']);
        $unrelated = Conversation::factory()->create(['status' => 'closed']);

        $this->actingAs($superAdmin)
            ->post(route('admin.users.open_conversations', $donor))
            ->assertRedirect();

        $this->assertDatabaseHas('conversations', ['id' => $closed->id, 'status' => 'open']);
        $this->assertDatabaseHas('conversations', ['id' => $unrelated->id, 'status' => 'closed']);
    }

    #[Test]
    public function regular_admin_cannot_reopen_a_users_conversations(): void
    {
        $admin = $this->admin();
        $donor = User::factory()->donor()->create();

        $this->actingAs($admin)
            ->post(route('admin.users.open_conversations', $donor))
            ->assertForbidden();
    }
}
