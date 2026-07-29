<?php

namespace Tests\Feature\Admin;

use App\Models\Glasses;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class GlassesManagementTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function admin_can_view_the_glasses_index(): void
    {
        $admin = User::factory()->admin()->create();
        Glasses::factory()->count(3)->create();

        $this->actingAs($admin)
            ->get(route('admin.glasses.index'))
            ->assertOk();
    }

    #[Test]
    public function super_admin_can_view_the_glasses_index(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        Glasses::factory()->count(2)->create();

        $this->actingAs($superAdmin)
            ->get(route('admin.glasses.index'))
            ->assertOk();
    }

    #[Test]
    public function non_admin_cannot_view_the_glasses_index(): void
    {
        $donor = User::factory()->donor()->create();

        $this->actingAs($donor)
            ->get(route('admin.glasses.index'))
            ->assertForbidden();
    }

    #[Test]
    public function admin_can_search_glasses_by_title(): void
    {
        $admin = User::factory()->admin()->create();
        Glasses::factory()->create(['title' => 'Ray-Ban Aviators']);
        Glasses::factory()->create(['title' => 'Reading Glasses +2.0']);

        $response = $this->actingAs($admin)
            ->get(route('admin.glasses.index', ['q' => 'Aviators']));

        $response->assertOk();
        $response->assertViewHas('glasses', fn ($glasses) => $glasses->total() === 1);
    }

    #[Test]
    public function admin_can_filter_glasses_by_status(): void
    {
        $admin = User::factory()->admin()->create();
        Glasses::factory()->available()->create();
        Glasses::factory()->create(['status' => 'donated']);

        $response = $this->actingAs($admin)
            ->get(route('admin.glasses.index', ['status' => 'donated']));

        $response->assertOk();
        $response->assertViewHas('glasses', fn ($glasses) => $glasses->total() === 1);
    }

    #[Test]
    public function admin_can_view_a_single_glasses_listing(): void
    {
        $admin = User::factory()->admin()->create();
        $glasses = Glasses::factory()->create();

        $this->actingAs($admin)
            ->get(route('admin.glasses.show', $glasses))
            ->assertOk();
    }

    #[Test]
    public function non_admin_cannot_view_a_single_glasses_listing(): void
    {
        $donor = User::factory()->donor()->create();
        $glasses = Glasses::factory()->create();

        $this->actingAs($donor)
            ->get(route('admin.glasses.show', $glasses))
            ->assertForbidden();
    }
}
