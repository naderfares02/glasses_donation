<?php

namespace Tests\Unit;

use App\Models\Glasses;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class UserModelTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function is_suspended_returns_true_only_when_status_is_suspended(): void
    {
        $active = User::factory()->create(['status' => 'active']);
        $suspended = User::factory()->suspended()->create();

        $this->assertFalse($active->isSuspended());
        $this->assertTrue($suspended->isSuspended());
    }

    #[Test]
    public function user_has_many_glasses(): void
    {
        $donor = User::factory()->donor()->create();
        Glasses::factory()->count(2)->create(['user_id' => $donor->id]);

        $this->assertCount(2, $donor->glasses);
    }

    #[Test]
    public function suspended_by_relationship_resolves_the_admin_who_suspended_the_user(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create([
            'status' => 'suspended',
            'suspended_by' => $admin->id,
        ]);

        $this->assertTrue($user->suspendedBy->is($admin));
    }

    #[Test]
    public function home_route_name_returns_the_donor_main_page_for_donors(): void
    {
        $donor = User::factory()->donor()->create();

        $this->assertSame('donor.main_page', $donor->homeRouteName());
    }

    #[Test]
    public function home_route_name_returns_the_recipient_main_page_for_recipients(): void
    {
        $recipient = User::factory()->recipient()->create();

        $this->assertSame('recipient.main_page', $recipient->homeRouteName());
    }

    #[Test]
    public function home_route_name_returns_the_admin_dashboard_for_admins_and_super_admins(): void
    {
        $admin = User::factory()->admin()->create();
        $superAdmin = User::factory()->superAdmin()->create();

        $this->assertSame('admin.dashboard', $admin->homeRouteName());
        $this->assertSame('admin.dashboard', $superAdmin->homeRouteName());
    }

    #[Test]
    public function home_route_name_falls_back_to_home_for_an_unknown_role(): void
    {
        // العمود role هو enum مقيّد بأربع قيم بقاعدة البيانات (donor/recipient/admin/super_admin)،
        // فما بنقدر نعمل create() بدور غير معروف. نستخدم make() لفحص منطق الدالة نفسه بس بدون حفظ.
        $user = User::factory()->make(['role' => 'something_unexpected']);

        $this->assertSame('home', $user->homeRouteName());
    }

    #[Test]
    public function every_role_returned_by_home_route_name_maps_to_a_route_that_actually_exists(): void
    {
        // يحمي من كسر route()-> إذا انحذف أو تغيّر اسم راوت بالمستقبل
        foreach (['donor', 'recipient', 'admin', 'super_admin'] as $role) {
            $user = User::factory()->create(['role' => $role]);

            $this->assertTrue(
                \Illuminate\Support\Facades\Route::has($user->homeRouteName()),
                "Route [{$user->homeRouteName()}] for role [{$role}] should exist."
            );
        }
    }
}
