<?php

namespace Tests\Feature\Roles;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class RoleAccessTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function guest_is_redirected_to_login_from_protected_routes(): void
    {
        $this->get(route('donor.main_page'))->assertRedirect(route('login'));
        $this->get(route('recipient.main_page'))->assertRedirect(route('login'));
        $this->get(route('admin.dashboard'))->assertRedirect(route('login'));
    }

    #[Test]
    public function recipient_cannot_access_donor_area(): void
    {
        $recipient = User::factory()->recipient()->create();

        $this->actingAs($recipient)
            ->get(route('donor.main_page'))
            ->assertForbidden();
    }

    #[Test]
    public function donor_cannot_access_recipient_area(): void
    {
        $donor = User::factory()->donor()->create();

        $this->actingAs($donor)
            ->get(route('recipient.main_page'))
            ->assertForbidden();
    }

    #[Test]
    public function non_admin_cannot_access_admin_area(): void
    {
        $donor = User::factory()->donor()->create();

        $this->actingAs($donor)
            ->get(route('admin.dashboard'))
            ->assertForbidden();
    }

    #[Test]
    public function admin_can_access_admin_dashboard(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk();
    }

    #[Test]
    public function only_super_admin_can_change_user_roles(): void
    {
        $admin = User::factory()->admin()->create();
        $target = User::factory()->recipient()->create();

        $this->actingAs($admin)
            ->post(route('admin.users.change_role', $target), ['role' => 'donor'])
            ->assertForbidden();

        $superAdmin = User::factory()->superAdmin()->create();

        $this->actingAs($superAdmin)
            ->post(route('admin.users.change_role', $target), ['role' => 'donor'])
            ->assertRedirect();

        $this->assertEquals('donor', $target->fresh()->role);
    }

    #[Test]
    public function suspended_user_is_logged_out_and_redirected(): void
    {
        $user = User::factory()->donor()->suspended()->create();

        $this->actingAs($user)
            ->get(route('donor.main_page'))
            ->assertRedirect(route('suspended'));

        $this->assertGuest();
    }
}
