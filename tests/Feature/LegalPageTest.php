<?php

namespace Tests\Feature\LegalPages;

use App\Models\LegalPage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class LegalPageTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function guest_can_view_the_terms_page(): void
    {
        LegalPage::factory()->terms()->create([
            'title' => 'Terms & Conditions',
        ]);

        $this->get(route('terms'))
            ->assertOk()
            ->assertSeeText('Terms & Conditions');
    }

    #[Test]
    public function guest_can_view_the_privacy_page(): void
    {
        LegalPage::factory()->privacy()->create([
            'title' => 'Privacy Policy',
        ]);

        $this->get(route('privacy'))
            ->assertOk()
            ->assertSeeText('Privacy Policy');
    }

    #[Test]
    public function terms_page_404s_when_no_record_exists_yet(): void
    {
        $this->get(route('terms'))->assertNotFound();
    }

    #[Test]
    public function privacy_page_404s_when_no_record_exists_yet(): void
    {
        $this->get(route('privacy'))->assertNotFound();
    }
}
