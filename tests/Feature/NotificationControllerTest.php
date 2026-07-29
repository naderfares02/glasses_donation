<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class NotificationControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Insert a raw database notification for a user without needing
     * a real Notification class to be dispatched.
     */
    private function makeNotification(User $user, array $data = [], ?\DateTimeInterface $readAt = null): DatabaseNotification
    {
        return DatabaseNotification::create([
            'id'              => (string) Str::uuid(),
            'type'            => 'App\\Notifications\\FakeNotification',
            'notifiable_type' => User::class,
            'notifiable_id'   => $user->id,
            'data'            => array_merge([
                'title'   => 'New Contact Request',
                'message' => 'Someone contacted you about your glasses.',
                'url'     => '/donor/main-page',
            ], $data),
            'read_at' => $readAt,
        ]);
    }

    #[Test]
    public function guest_cannot_view_notifications(): void
    {
        $this->get(route('notifications.index'))->assertRedirect(route('login'));
    }

    #[Test]
    public function index_defaults_to_the_unread_tab(): void
    {
        $donor = User::factory()->donor()->create();
        $this->makeNotification($donor); // unread
        $this->makeNotification($donor, [], now()); // read

        $response = $this->actingAs($donor)->get(route('notifications.index'));

        $response->assertOk();
        $response->assertViewHas('tab', 'unread');
        $response->assertViewHas('notifications', fn ($notifications) => $notifications->total() === 1);
    }

    #[Test]
    public function all_tab_shows_read_and_unread_notifications(): void
    {
        $donor = User::factory()->donor()->create();
        $this->makeNotification($donor);
        $this->makeNotification($donor, [], now());

        $response = $this->actingAs($donor)->get(route('notifications.index', ['tab' => 'all']));

        $response->assertOk();
        $response->assertViewHas('notifications', fn ($notifications) => $notifications->total() === 2);
    }

    #[Test]
    public function index_reports_correct_unread_and_total_counts(): void
    {
        $donor = User::factory()->donor()->create();
        $this->makeNotification($donor);
        $this->makeNotification($donor);
        $this->makeNotification($donor, [], now());

        $response = $this->actingAs($donor)->get(route('notifications.index'));

        $response->assertViewHas('counts', ['unread' => 2, 'all' => 3]);
    }

    #[Test]
    public function user_only_sees_their_own_notifications(): void
    {
        $donor = User::factory()->donor()->create();
        $otherDonor = User::factory()->donor()->create();

        $this->makeNotification($otherDonor);

        $response = $this->actingAs($donor)->get(route('notifications.index', ['tab' => 'all']));

        $response->assertViewHas('notifications', fn ($notifications) => $notifications->total() === 0);
    }

    #[Test]
    public function marking_a_notification_read_redirects_to_its_url_when_present(): void
    {
        $donor = User::factory()->donor()->create();
        $notification = $this->makeNotification($donor, ['url' => '/donor/main-page']);

        $this->actingAs($donor)
            ->post(route('notifications.read', $notification->id))
            ->assertRedirect('/donor/main-page');

        $this->assertNotNull($notification->fresh()->read_at);
    }

    #[Test]
    public function marking_a_notification_read_without_a_url_redirects_back(): void
    {
        $donor = User::factory()->donor()->create();
        $notification = $this->makeNotification($donor, ['url' => null]);

        $this->actingAs($donor)
            ->post(route('notifications.read', $notification->id))
            ->assertRedirect();

        $this->assertNotNull($notification->fresh()->read_at);
    }

    #[Test]
    public function marking_someone_elses_notification_read_returns_not_found(): void
    {
        $donor = User::factory()->donor()->create();
        $otherDonor = User::factory()->donor()->create();
        $notification = $this->makeNotification($otherDonor);

        $this->actingAs($donor)
            ->post(route('notifications.read', $notification->id))
            ->assertNotFound();

        $this->assertNull($notification->fresh()->read_at);
    }

    #[Test]
    public function opening_a_notification_marks_it_read_and_redirects(): void
    {
        // ⚠️ This route is wired to NotificationController::open() in routes/web.php,
        // but that method does not exist on the controller (only index/markRead/markAllRead do).
        // This test documents the expected behavior and will fail until that's fixed.
        $donor = User::factory()->donor()->create();
        $notification = $this->makeNotification($donor, ['url' => '/donor/main-page']);

        $this->actingAs($donor)
            ->get(route('notifications.open', $notification->id))
            ->assertRedirect('/donor/main-page');

        $this->assertNotNull($notification->fresh()->read_at);
    }

    #[Test]
    public function mark_all_read_marks_every_unread_notification(): void
    {
        $donor = User::factory()->donor()->create();
        $first = $this->makeNotification($donor);
        $second = $this->makeNotification($donor);

        $this->actingAs($donor)
            ->post(route('notifications.read_all'))
            ->assertRedirect();

        $this->assertNotNull($first->fresh()->read_at);
        $this->assertNotNull($second->fresh()->read_at);
    }

    #[Test]
    public function mark_all_read_does_not_touch_other_users_notifications(): void
    {
        $donor = User::factory()->donor()->create();
        $otherDonor = User::factory()->donor()->create();
        $othersNotification = $this->makeNotification($otherDonor);

        $this->actingAs($donor)->post(route('notifications.read_all'));

        $this->assertNull($othersNotification->fresh()->read_at);
    }
}
