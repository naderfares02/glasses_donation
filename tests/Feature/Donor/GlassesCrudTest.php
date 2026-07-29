<?php

namespace Tests\Feature\Donor;

use App\Models\Glasses;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class GlassesCrudTest extends TestCase
{
    use RefreshDatabase;

    protected function donor(): User
    {
        return User::factory()->donor()->create();
    }

    #[Test]
    public function donor_can_view_their_glasses_list(): void
    {
        $donor = $this->donor();
        Glasses::factory()->count(3)->create(['user_id' => $donor->id]);

        $this->actingAs($donor)
            ->get(route('donor.glasses.index'))
            ->assertOk();
    }

    #[Test]
    public function donor_can_create_a_glasses_listing(): void
    {
        Storage::fake('public');
        $donor = $this->donor();

        $payload = [
            'title' => 'نظارة طبية للاستخدام اليومي',
            'description' => 'حالة جيدة جداً تقريباً بدون خدوش',
            'condition' => 'used',
            'brand' => 'Ray-Ban',
            'lens_type' => 'single_vision',
            'vision_type' => 'distance',
            'frame_size' => 'medium',
            'frame_color' => 'black',
            'age_group' => 'adult',
            'gender' => 'unisex',
            'pickup_city' => 'نابلس',
            'contact_method' => 'chat_only',
        ];

        $response = $this->actingAs($donor)
            ->post(route('donor.glasses.store'), $payload);

        $response->assertRedirect();
        $this->assertDatabaseHas('glasses', [
            'user_id' => $donor->id,
            'title' => $payload['title'],
            'status' => 'available',
        ]);
    }

    #[Test]
    public function donor_cannot_edit_glasses_belonging_to_another_donor(): void
    {
        $donor = $this->donor();
        $otherDonor = $this->donor();
        $glasses = Glasses::factory()->create(['user_id' => $otherDonor->id]);

        $this->actingAs($donor)
            ->get(route('donor.glasses.edit', $glasses))
            ->assertForbidden();
    }

    #[Test]
    public function donor_can_update_their_own_glasses(): void
    {
        $donor = $this->donor();
        $glasses = Glasses::factory()->create(['user_id' => $donor->id]);

        $this->actingAs($donor)
            ->put(route('donor.glasses.update', $glasses), [
                'title' => 'عنوان محدّث',
                'description' => $glasses->description,
                'condition' => $glasses->condition,
                'contact_method' => 'phone',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('glasses', [
            'id' => $glasses->id,
            'title' => 'عنوان محدّث',
        ]);
    }

    #[Test]
    public function donor_can_delete_their_own_glasses(): void
    {
        $donor = $this->donor();
        $glasses = Glasses::factory()->create(['user_id' => $donor->id]);

        $this->actingAs($donor)
            ->delete(route('donor.glasses.destroy', $glasses))
            ->assertRedirect();

        $this->assertDatabaseMissing('glasses', ['id' => $glasses->id]);
    }

    #[Test]
    public function creating_glasses_requires_mandatory_fields(): void
    {
        $donor = $this->donor();

        $this->actingAs($donor)
            ->post(route('donor.glasses.store'), [])
            ->assertSessionHasErrors(['title', 'condition']);
    }
}
