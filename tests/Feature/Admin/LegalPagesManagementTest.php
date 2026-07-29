<?php

namespace Tests\Feature\Admin;

use App\Models\LegalPage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class LegalPagesManagementTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function super_admin_can_view_the_legal_pages_index(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        LegalPage::factory()->terms()->create();
        LegalPage::factory()->privacy()->create();

        $this->actingAs($superAdmin)
            ->get(route('admin.legal.index'))
            ->assertOk();
    }

    #[Test]
    public function regular_admin_cannot_view_the_legal_pages_index(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get(route('admin.legal.index'))
            ->assertForbidden();
    }

    #[Test]
    public function non_admin_cannot_view_the_legal_pages_index(): void
    {
        $donor = User::factory()->donor()->create();

        $this->actingAs($donor)
            ->get(route('admin.legal.index'))
            ->assertForbidden();
    }

    #[Test]
    public function super_admin_can_view_the_edit_form(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $page = LegalPage::factory()->terms()->create();

        $this->actingAs($superAdmin)
            ->get(route('admin.legal.edit', $page))
            ->assertOk();
    }

    #[Test]
    public function super_admin_can_update_a_legal_page(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $page = LegalPage::factory()->terms()->unpublished()->create();

        $this->actingAs($superAdmin)
            ->put(route('admin.legal.update', $page), [
                'title'   => 'Updated Terms',
                'content' => '<p>Updated content</p>',
                'publish' => true,
            ])
            ->assertRedirect();

        $page->refresh();

        $this->assertEquals('Updated Terms', $page->title);
        $this->assertEquals('<p>Updated content</p>', $page->content);
        $this->assertEquals($superAdmin->id, $page->updated_by);
        $this->assertNotNull($page->published_at);
    }

    #[Test]
    public function updating_without_publish_keeps_the_page_unpublished(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $page = LegalPage::factory()->terms()->unpublished()->create();

        $this->actingAs($superAdmin)
            ->put(route('admin.legal.update', $page), [
                'title'   => 'Updated Terms',
                'content' => '<p>Updated content</p>',
            ])
            ->assertRedirect();

        $this->assertNull($page->fresh()->published_at);
    }

    #[Test]
    public function updating_a_legal_page_requires_title_and_content(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $page = LegalPage::factory()->terms()->create();

        $this->actingAs($superAdmin)
            ->put(route('admin.legal.update', $page), [
                'title'   => '',
                'content' => '',
            ])
            ->assertSessionHasErrors(['title', 'content']);
    }

    #[Test]
    public function regular_admin_cannot_update_a_legal_page(): void
    {
        $admin = User::factory()->admin()->create();
        $page = LegalPage::factory()->terms()->create();

        $this->actingAs($admin)
            ->put(route('admin.legal.update', $page), [
                'title'   => 'Hacked Title',
                'content' => '<p>Hacked</p>',
            ])
            ->assertForbidden();

        $this->assertNotEquals('Hacked Title', $page->fresh()->title);
    }
}
