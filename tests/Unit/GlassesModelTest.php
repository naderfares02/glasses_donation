<?php

namespace Tests\Unit;

use App\Models\Glasses;
use App\Models\GlassesImage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class GlassesModelTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function primary_image_returns_only_the_image_marked_as_primary(): void
    {
        $glasses = Glasses::factory()->create();

        GlassesImage::factory()->create(['glasses_id' => $glasses->id, 'is_primary' => false]);
        $primary = GlassesImage::factory()->primary()->create(['glasses_id' => $glasses->id]);
        GlassesImage::factory()->create(['glasses_id' => $glasses->id, 'is_primary' => false]);

        $this->assertTrue($glasses->primaryImage->is($primary));
    }

    #[Test]
    public function primary_image_is_null_when_no_image_is_marked_as_primary(): void
    {
        $glasses = Glasses::factory()->create();
        GlassesImage::factory()->count(2)->create(['glasses_id' => $glasses->id, 'is_primary' => false]);

        $this->assertNull($glasses->primaryImage);
    }

    #[Test]
    public function images_returns_all_images_regardless_of_primary_flag(): void
    {
        $glasses = Glasses::factory()->create();
        GlassesImage::factory()->count(2)->create(['glasses_id' => $glasses->id, 'is_primary' => false]);
        GlassesImage::factory()->primary()->create(['glasses_id' => $glasses->id]);

        $this->assertCount(3, $glasses->images);
    }

    #[Test]
    public function donor_alias_resolves_to_the_owning_user(): void
    {
        $glasses = Glasses::factory()->create();

        $this->assertTrue($glasses->donor->is($glasses->user));
    }
}
