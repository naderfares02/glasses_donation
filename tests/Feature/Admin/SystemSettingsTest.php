<?php

namespace Tests\Feature\Admin;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SystemSettingsTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function super_admin_can_view_the_settings_page(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();

        $this->actingAs($superAdmin)
            ->get(route('admin.settings.index'))
            ->assertOk()
            ->assertViewHas('isDown', false);
    }

    #[Test]
    public function regular_admin_cannot_view_the_settings_page(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get(route('admin.settings.index'))
            ->assertForbidden();
    }

    #[Test]
    public function non_admin_cannot_view_the_settings_page(): void
    {
        $donor = User::factory()->donor()->create();

        $this->actingAs($donor)
            ->get(route('admin.settings.index'))
            ->assertForbidden();
    }

    #[Test]
    public function super_admin_can_update_settings(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();

        $this->actingAs($superAdmin)
            ->post(route('admin.settings.update'), [
                'site_name'   => 'Glasses Donation Platform',
                'support_email' => 'support@example.com',
                'allow_registration' => '1',
                'require_phone_verification' => '0',
                'require_admin_approval_for_donated' => '1',
            ])
            ->assertRedirect();

        $this->assertEquals(['v' => 'Glasses Donation Platform'], Setting::where('key', 'site.name')->first()->value);
        $this->assertEquals(['v' => 'support@example.com'], Setting::where('key', 'site.support_email')->first()->value);
        $this->assertEquals(['v' => true], Setting::where('key', 'auth.allow_registration')->first()->value);
        $this->assertEquals(['v' => false], Setting::where('key', 'auth.require_phone_verification')->first()->value);
        $this->assertEquals(['v' => true], Setting::where('key', 'donations.require_admin_approval_for_donated')->first()->value);
    }

    #[Test]
    public function updated_settings_are_reflected_back_on_the_settings_page(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();

        $this->actingAs($superAdmin)->post(route('admin.settings.update'), [
            'site_name'   => 'My Platform',
            'support_email' => 'help@example.com',
            'allow_registration' => '0',
            'require_phone_verification' => '1',
            'require_admin_approval_for_donated' => '0',
        ]);

        $this->actingAs($superAdmin)
            ->get(route('admin.settings.index'))
            ->assertViewHas('site_name', 'My Platform')
            ->assertViewHas('support_email', 'help@example.com')
            ->assertViewHas('allow_registration', false)
            ->assertViewHas('require_phone_verification', true)
            ->assertViewHas('require_admin_approval_for_donated', false);
    }

    #[Test]
    public function updating_settings_requires_a_site_name(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();

        $this->actingAs($superAdmin)
            ->post(route('admin.settings.update'), [
                'site_name' => '',
                'allow_registration' => '1',
                'require_phone_verification' => '0',
                'require_admin_approval_for_donated' => '1',
            ])
            ->assertSessionHasErrors(['site_name']);
    }

    #[Test]
    public function updating_settings_rejects_an_invalid_support_email(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();

        $this->actingAs($superAdmin)
            ->post(route('admin.settings.update'), [
                'site_name' => 'My Platform',
                'support_email' => 'not-an-email',
                'allow_registration' => '1',
                'require_phone_verification' => '0',
                'require_admin_approval_for_donated' => '1',
            ])
            ->assertSessionHasErrors(['support_email']);
    }

    #[Test]
    public function regular_admin_cannot_update_settings(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->post(route('admin.settings.update'), [
                'site_name' => 'Hacked Name',
                'allow_registration' => '1',
                'require_phone_verification' => '0',
                'require_admin_approval_for_donated' => '1',
            ])
            ->assertForbidden();

        $this->assertNull(Setting::where('key', 'site.name')->first());
    }

    #[Test]
    public function super_admin_can_enable_maintenance_mode(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();

        $this->actingAs($superAdmin)
            ->post(route('admin.settings.maintenance.on'))
            ->assertRedirect();

        $this->assertEquals(['v' => true], Setting::where('key', 'site.maintenance')->first()->value);
    }

    #[Test]
    public function super_admin_can_disable_maintenance_mode(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        Setting::create(['key' => 'site.maintenance', 'type' => 'bool', 'value' => ['v' => true]]);

        $this->actingAs($superAdmin)
            ->post(route('admin.settings.maintenance.off'))
            ->assertRedirect();

        $this->assertEquals(['v' => false], Setting::where('key', 'site.maintenance')->first()->value);
    }

    #[Test]
    public function regular_admin_cannot_toggle_maintenance_mode(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->post(route('admin.settings.maintenance.on'))
            ->assertForbidden();

        $this->assertNull(Setting::where('key', 'site.maintenance')->first());
    }

    #[Test]
    public function maintenance_toggle_is_rate_limited(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();

        for ($i = 0; $i < 5; $i++) {
            $this->actingAs($superAdmin)->post(route('admin.settings.maintenance.on'));
        }

        $this->actingAs($superAdmin)
            ->post(route('admin.settings.maintenance.on'))
            ->assertStatus(429);
    }

    #[Test]
    public function super_admin_can_clear_the_cache(): void
    {
        Artisan::shouldReceive('call')->once()->with('optimize:clear')->andReturn(0);

        $superAdmin = User::factory()->superAdmin()->create();

        $this->actingAs($superAdmin)
            ->post(route('admin.system.cache.clear'))
            ->assertRedirect();
    }

    #[Test]
    public function regular_admin_cannot_clear_the_cache(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->post(route('admin.system.cache.clear'))
            ->assertForbidden();
    }

    #[Test]
    public function super_admin_can_run_optimize(): void
    {
        Artisan::shouldReceive('call')->once()->with('optimize')->andReturn(0);

        $superAdmin = User::factory()->superAdmin()->create();

        $this->actingAs($superAdmin)
            ->post(route('admin.system.optimize'))
            ->assertRedirect();
    }
}