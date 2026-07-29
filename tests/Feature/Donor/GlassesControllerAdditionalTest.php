<?php

namespace Tests\Feature\Donor;

use App\Models\Glasses;
use App\Models\GlassesImage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class GlassesControllerAdditionalTest extends TestCase
{
    use RefreshDatabase;

    protected function donor(): User
    {
        return User::factory()->donor()->create();
    }

    /*
    |--------------------------------------------------------------------------
    | show()
    |--------------------------------------------------------------------------
    */

    #[Test]
    public function donor_can_view_their_own_glasses(): void
    {
        $donor = $this->donor();
        $glasses = Glasses::factory()->create(['user_id' => $donor->id]);

        $this->actingAs($donor)
            ->get(route('donor.glasses.show', $glasses))
            ->assertOk()
            ->assertViewIs('donor.glasses_show')
            ->assertViewHas('glasses');
    }

    #[Test]
    public function donor_cannot_view_glasses_belonging_to_another_donor(): void
    {
        $donor = $this->donor();
        $otherDonor = $this->donor();
        $glasses = Glasses::factory()->create(['user_id' => $otherDonor->id]);

        $this->actingAs($donor)
            ->get(route('donor.glasses.show', $glasses))
            ->assertForbidden();
    }

    /*
    |--------------------------------------------------------------------------
    | destroy()
    |--------------------------------------------------------------------------
    */

    #[Test]
    public function deleting_glasses_removes_their_images_from_storage(): void
    {
        Storage::fake('public');
        $donor = $this->donor();
        $glasses = Glasses::factory()->create(['user_id' => $donor->id]);

        $file = UploadedFile::fake()->image('glasses.jpg');
        $path = $file->store('glasses', 'public');

        GlassesImage::factory()->create([
            'glasses_id' => $glasses->id,
            'path' => $path,
            'is_primary' => true,
        ]);

        $this->actingAs($donor)
            ->delete(route('donor.glasses.destroy', $glasses))
            ->assertRedirect(route('donor.glasses.index'));

        Storage::disk('public')->assertMissing($path);
        $this->assertDatabaseMissing('glasses', ['id' => $glasses->id]);
    }

    #[Test]
    public function donor_gets_not_found_deleting_glasses_they_do_not_own(): void
    {
        $donor = $this->donor();
        $otherDonor = $this->donor();
        $glasses = Glasses::factory()->create(['user_id' => $otherDonor->id]);

        $this->actingAs($donor)
            ->delete(route('donor.glasses.destroy', $glasses))
            ->assertNotFound();

        $this->assertDatabaseHas('glasses', ['id' => $glasses->id]);
    }

    /*
    |--------------------------------------------------------------------------
    | destroyImage()
    |--------------------------------------------------------------------------
    */

    #[Test]
    public function donor_can_delete_a_non_primary_image(): void
    {
        Storage::fake('public');
        $donor = $this->donor();
        $glasses = Glasses::factory()->create(['user_id' => $donor->id]);

        $file = UploadedFile::fake()->image('extra.jpg');
        $path = $file->store('glasses', 'public');

        $image = GlassesImage::factory()->create([
            'glasses_id' => $glasses->id,
            'path' => $path,
            'is_primary' => false,
        ]);

        $this->actingAs($donor)
            ->delete(route('donor.glasses.images.destroy', ['glasses' => $glasses->id, 'image' => $image->id]))
            ->assertRedirect();

        Storage::disk('public')->assertMissing($path);
        $this->assertDatabaseMissing('glasses_images', ['id' => $image->id]);
    }

    #[Test]
    public function donor_cannot_delete_the_primary_image_via_this_endpoint(): void
    {
        Storage::fake('public');
        $donor = $this->donor();
        $glasses = Glasses::factory()->create(['user_id' => $donor->id]);

        $image = GlassesImage::factory()->create([
            'glasses_id' => $glasses->id,
            'is_primary' => true,
        ]);

        $this->actingAs($donor)
            ->delete(route('donor.glasses.images.destroy', ['glasses' => $glasses->id, 'image' => $image->id]))
            ->assertNotFound();

        $this->assertDatabaseHas('glasses_images', ['id' => $image->id]);
    }

    #[Test]
    public function donor_cannot_delete_an_image_belonging_to_another_donors_glasses(): void
    {
        $donor = $this->donor();
        $otherDonor = $this->donor();
        $glasses = Glasses::factory()->create(['user_id' => $otherDonor->id]);
        $image = GlassesImage::factory()->create(['glasses_id' => $glasses->id, 'is_primary' => false]);

        $this->actingAs($donor)
            ->delete(route('donor.glasses.images.destroy', ['glasses' => $glasses->id, 'image' => $image->id]))
            ->assertNotFound();

        $this->assertDatabaseHas('glasses_images', ['id' => $image->id]);
    }

    #[Test]
    public function donor_cannot_delete_an_image_that_belongs_to_a_different_glasses(): void
    {
        $donor = $this->donor();
        $glasses = Glasses::factory()->create(['user_id' => $donor->id]);
        $otherGlasses = Glasses::factory()->create(['user_id' => $donor->id]);
        $image = GlassesImage::factory()->create(['glasses_id' => $otherGlasses->id, 'is_primary' => false]);

        $this->actingAs($donor)
            ->delete(route('donor.glasses.images.destroy', ['glasses' => $glasses->id, 'image' => $image->id]))
            ->assertNotFound();

        $this->assertDatabaseHas('glasses_images', ['id' => $image->id]);
    }
}
