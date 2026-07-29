<?php

namespace Tests\Feature\Donor;

use App\Models\Conversation;
use App\Models\DonationRequest;
use App\Models\Glasses;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class DashboardControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function donor(): User
    {
        return User::factory()->donor()->create();
    }

    #[Test]
    public function donor_can_view_the_main_page(): void
    {
        $donor = $this->donor();

        $this->actingAs($donor)
            ->get(route('donor.main_page'))
            ->assertOk()
            ->assertViewIs('donor.main_page')
            ->assertViewHas('stats');
    }

    #[Test]
    public function it_reports_correct_glasses_status_counts(): void
    {
        $donor = $this->donor();

        Glasses::factory()->count(2)->create(['user_id' => $donor->id, 'status' => 'available']);
        Glasses::factory()->count(1)->create(['user_id' => $donor->id, 'status' => 'reserved']);
        Glasses::factory()->count(3)->create(['user_id' => $donor->id, 'status' => 'in_contact']);
        Glasses::factory()->count(1)->create(['user_id' => $donor->id, 'status' => 'pending_donation']);
        Glasses::factory()->count(2)->create(['user_id' => $donor->id, 'status' => 'donated']);

        $response = $this->actingAs($donor)->get(route('donor.main_page'));

        $stats = $response->viewData('stats');

        $this->assertSame(9, $stats['glasses_total']);
        $this->assertSame(2, $stats['available']);
        $this->assertSame(1, $stats['reserved']);
        $this->assertSame(3, $stats['in_contact']);
        $this->assertSame(1, $stats['pending_donation']);
        $this->assertSame(2, $stats['donated']);
    }

    #[Test]
    public function glasses_stats_are_scoped_to_the_authenticated_donor(): void
    {
        $donor = $this->donor();
        $otherDonor = $this->donor();

        Glasses::factory()->count(2)->create(['user_id' => $donor->id, 'status' => 'available']);
        Glasses::factory()->count(5)->create(['user_id' => $otherDonor->id, 'status' => 'available']);

        $response = $this->actingAs($donor)->get(route('donor.main_page'));

        $stats = $response->viewData('stats');

        $this->assertSame(2, $stats['glasses_total']);
        $this->assertSame(2, $stats['available']);
    }

    #[Test]
    public function it_counts_only_open_conversations_for_the_donor(): void
    {
        $donor = $this->donor();
        $otherDonor = $this->donor();

        Conversation::factory()->count(2)->create(['donor_id' => $donor->id, 'status' => 'open']);
        Conversation::factory()->create(['donor_id' => $donor->id, 'status' => 'closed']);
        Conversation::factory()->create(['donor_id' => $otherDonor->id, 'status' => 'open']);

        $response = $this->actingAs($donor)->get(route('donor.main_page'));

        $this->assertSame(2, $response->viewData('stats')['conversations_open']);
    }

    #[Test]
    public function it_reports_donation_request_counts_by_status(): void
    {
        $donor = $this->donor();
        $otherDonor = $this->donor();

        DonationRequest::factory()->count(2)->create(['donor_id' => $donor->id, 'status' => 'pending']);
        DonationRequest::factory()->count(3)->create(['donor_id' => $donor->id, 'status' => 'approved']);
        DonationRequest::factory()->count(1)->create(['donor_id' => $donor->id, 'status' => 'rejected']);
        DonationRequest::factory()->create(['donor_id' => $otherDonor->id, 'status' => 'pending']);

        $response = $this->actingAs($donor)->get(route('donor.main_page'));
        $stats = $response->viewData('stats');

        $this->assertSame(2, $stats['donation_requests_pending']);
        $this->assertSame(3, $stats['donation_requests_approved']);
        $this->assertSame(1, $stats['donation_requests_rejected']);
    }

    #[Test]
    public function a_new_donor_with_no_activity_sees_all_zero_stats(): void
    {
        $donor = $this->donor();

        $response = $this->actingAs($donor)->get(route('donor.main_page'));
        $stats = $response->viewData('stats');

        foreach ($stats as $key => $value) {
            $this->assertSame(0, $value, "Expected stat [{$key}] to be 0.");
        }
    }
}
