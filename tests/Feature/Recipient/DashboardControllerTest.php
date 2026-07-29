<?php

namespace Tests\Feature\Recipient;

use App\Models\Glasses;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class DashboardControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function recipient(): User
    {
        return User::factory()->recipient()->create();
    }

    #[Test]
    public function recipient_can_view_the_main_page(): void
    {
        $recipient = $this->recipient();

        $this->actingAs($recipient)
            ->get(route('recipient.main_page'))
            ->assertOk()
            ->assertViewIs('recipient.main_page')
            ->assertViewHas('glasses');
    }

    #[Test]
    public function only_available_glasses_are_listed(): void
    {
        $recipient = $this->recipient();

        $available = Glasses::factory()->create(['status' => 'available', 'title' => 'نظارة متاحة']);
        Glasses::factory()->create(['status' => 'reserved', 'title' => 'نظارة محجوزة']);
        Glasses::factory()->create(['status' => 'donated', 'title' => 'نظارة متبرع بها']);
        Glasses::factory()->create(['status' => 'in_contact', 'title' => 'نظارة قيد التواصل']);
        Glasses::factory()->create(['status' => 'pending_donation', 'title' => 'نظارة قيد الإتمام']);

        $response = $this->actingAs($recipient)->get(route('recipient.main_page'));

        $response->assertOk();
        $ids = $response->viewData('glasses')->pluck('id');

        $this->assertTrue($ids->contains($available->id));
        $this->assertCount(1, $ids);
    }

    #[Test]
    public function it_searches_by_title(): void
    {
        $recipient = $this->recipient();

        $match = Glasses::factory()->create(['status' => 'available', 'title' => 'نظارة طبية زرقاء']);
        Glasses::factory()->create(['status' => 'available', 'title' => 'نظارة شمسية سوداء']);

        $response = $this->actingAs($recipient)
            ->get(route('recipient.main_page', ['q' => 'زرقاء']));

        $ids = $response->viewData('glasses')->pluck('id');

        $this->assertTrue($ids->contains($match->id));
        $this->assertCount(1, $ids);
    }

    #[Test]
    public function it_searches_by_serial_number(): void
    {
        $recipient = $this->recipient();

        $match = Glasses::factory()->create([
            'status' => 'available',
            'serial_number' => 'ABC-12345',
        ]);
        Glasses::factory()->create([
            'status' => 'available',
            'serial_number' => 'XYZ-99999',
        ]);

        $response = $this->actingAs($recipient)
            ->get(route('recipient.main_page', ['q' => 'ABC-123']));

        $ids = $response->viewData('glasses')->pluck('id');

        $this->assertTrue($ids->contains($match->id));
        $this->assertCount(1, $ids);
    }

    #[Test]
    public function it_searches_by_brand(): void
    {
        $recipient = $this->recipient();

        $match = Glasses::factory()->create(['status' => 'available', 'brand' => 'Ray-Ban']);
        Glasses::factory()->create(['status' => 'available', 'brand' => 'Oakley']);

        $response = $this->actingAs($recipient)
            ->get(route('recipient.main_page', ['q' => 'Ray-Ban']));

        $ids = $response->viewData('glasses')->pluck('id');

        $this->assertTrue($ids->contains($match->id));
        $this->assertCount(1, $ids);
    }

    #[Test]
    public function it_searches_by_donor_name(): void
    {
        $recipient = $this->recipient();

        $donor = User::factory()->donor()->create(['name' => 'أحمد الدونر']);
        $match = Glasses::factory()->create(['status' => 'available', 'user_id' => $donor->id]);
        Glasses::factory()->create(['status' => 'available']);

        $response = $this->actingAs($recipient)
            ->get(route('recipient.main_page', ['q' => 'أحمد']));

        $ids = $response->viewData('glasses')->pluck('id');

        $this->assertTrue($ids->contains($match->id));
        $this->assertCount(1, $ids);
    }

    #[Test]
    public function it_filters_by_valid_condition(): void
    {
        $recipient = $this->recipient();

        $new = Glasses::factory()->create(['status' => 'available', 'condition' => 'new']);
        Glasses::factory()->create(['status' => 'available', 'condition' => 'used']);

        $response = $this->actingAs($recipient)
            ->get(route('recipient.main_page', ['condition' => 'new']));

        $ids = $response->viewData('glasses')->pluck('id');

        $this->assertTrue($ids->contains($new->id));
        $this->assertCount(1, $ids);
    }

    #[Test]
    public function an_invalid_condition_value_is_ignored(): void
    {
        $recipient = $this->recipient();

        Glasses::factory()->count(2)->create(['status' => 'available']);

        $response = $this->actingAs($recipient)
            ->get(route('recipient.main_page', ['condition' => 'not-a-real-condition']));

        $this->assertCount(2, $response->viewData('glasses'));
    }

    #[Test]
    public function it_filters_by_lens_type(): void
    {
        $recipient = $this->recipient();

        $match = Glasses::factory()->create(['status' => 'available', 'lens_type' => 'bifocal']);
        Glasses::factory()->create(['status' => 'available', 'lens_type' => 'progressive']);

        $response = $this->actingAs($recipient)
            ->get(route('recipient.main_page', ['lens_type' => 'bifocal']));

        $ids = $response->viewData('glasses')->pluck('id');

        $this->assertTrue($ids->contains($match->id));
        $this->assertCount(1, $ids);
    }

    #[Test]
    public function search_and_filters_can_be_combined(): void
    {
        $recipient = $this->recipient();

        $match = Glasses::factory()->create([
            'status' => 'available',
            'title' => 'نظارة قراءة مميزة',
            'condition' => 'new',
            'lens_type' => 'reading',
        ]);

        Glasses::factory()->create([
            'status' => 'available',
            'title' => 'نظارة قراءة مميزة',
            'condition' => 'used',
            'lens_type' => 'reading',
        ]);

        $response = $this->actingAs($recipient)->get(route('recipient.main_page', [
            'q' => 'قراءة',
            'condition' => 'new',
            'lens_type' => 'reading',
        ]));

        $ids = $response->viewData('glasses')->pluck('id');

        $this->assertTrue($ids->contains($match->id));
        $this->assertCount(1, $ids);
    }

    #[Test]
    public function results_are_paginated_at_sixteen_per_page(): void
    {
        $recipient = $this->recipient();

        Glasses::factory()->count(20)->create(['status' => 'available']);

        $response = $this->actingAs($recipient)->get(route('recipient.main_page'));

        $glasses = $response->viewData('glasses');

        $this->assertCount(16, $glasses->items());
        $this->assertEquals(20, $glasses->total());
    }

    #[Test]
    public function results_are_ordered_latest_first(): void
    {
        $recipient = $this->recipient();

        $older = Glasses::factory()->create(['status' => 'available', 'created_at' => now()->subDay()]);
        $newer = Glasses::factory()->create(['status' => 'available', 'created_at' => now()]);

        $response = $this->actingAs($recipient)->get(route('recipient.main_page'));

        $ids = $response->viewData('glasses')->pluck('id')->values();

        $this->assertEquals($newer->id, $ids->first());
        $this->assertEquals($older->id, $ids->last());
    }

    #[Test]
    public function query_string_is_preserved_across_pagination(): void
    {
        $recipient = $this->recipient();

        Glasses::factory()->count(20)->create(['status' => 'available', 'condition' => 'new']);

        $response = $this->actingAs($recipient)->get(route('recipient.main_page', ['condition' => 'new']));

        $glasses = $response->viewData('glasses');

        $this->assertStringContainsString('condition=new', $glasses->nextPageUrl());
    }
}
