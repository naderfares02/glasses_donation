<?php

namespace Tests\Feature\Admin;

use App\Models\DonationRequest;
use App\Models\LegalPage;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ControlControllerTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function super_admin_can_view_the_control_page(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();

        $this->actingAs($superAdmin)
            ->get(route('admin.control'))
            ->assertOk()
            ->assertViewHas('isSuperAdmin', true);
    }

    #[Test]
    public function regular_admin_cannot_view_the_control_page(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get(route('admin.control'))
            ->assertForbidden();
    }

    #[Test]
    public function non_admin_cannot_view_the_control_page(): void
    {
        $donor = User::factory()->donor()->create();

        $this->actingAs($donor)
            ->get(route('admin.control'))
            ->assertForbidden();
    }

    #[Test]
    public function it_reports_accurate_platform_counts(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        User::factory()->admin()->create();

        User::factory()->donor()->suspended()->create();
        User::factory()->recipient()->suspended()->create();
        User::factory()->donor()->create(); // active, should not be counted

        DonationRequest::factory()->count(2)->create(['status' => 'pending']);
        DonationRequest::factory()->create(['status' => 'approved']);

        $response = $this->actingAs($superAdmin)->get(route('admin.control'));

        $response->assertViewHas('counts', [
            'admins'            => 2, // superAdmin + admin
            'pending_donations' => 2,
            'suspended'         => 2,
        ]);
    }

    #[Test]
    public function it_reflects_the_current_maintenance_flag(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        Setting::create(['key' => 'site.maintenance', 'type' => 'bool', 'value' => ['v' => true]]);

        $this->actingAs($superAdmin)
            ->get(route('admin.control'))
            ->assertViewHas('isDown', true);
    }

    #[Test]
    public function maintenance_defaults_to_off_when_no_setting_exists(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();

        $this->actingAs($superAdmin)
            ->get(route('admin.control'))
            ->assertViewHas('isDown', false);
    }

    #[Test]
    public function it_only_exposes_the_terms_and_privacy_legal_pages(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();

        LegalPage::factory()->terms()->create();
        LegalPage::factory()->privacy()->create();
        LegalPage::factory()->create(['key' => 'cookies']); // should be ignored

        $response = $this->actingAs($superAdmin)->get(route('admin.control'));

        $response->assertViewHas('legal', function ($legal) {
            return $legal->has('terms')
                && $legal->has('privacy')
                && !$legal->has('cookies')
                && $legal->count() === 2;
        });
    }
}
