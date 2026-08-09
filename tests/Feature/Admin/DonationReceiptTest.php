<?php

namespace Tests\Feature\Admin;

use App\Models\DonationReceipt;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DonationReceiptTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function admin_can_view_any_receipt(): void
    {
        $admin = User::factory()->admin()->create();
        $receipt = DonationReceipt::factory()->create();

        $this->actingAs($admin)
            ->get(route('admin.receipts.show', $receipt))
            ->assertOk();
    }

    #[Test]
    public function donor_cannot_access_admin_receipt_route(): void
    {
        $donor = User::factory()->donor()->create();
        $receipt = DonationReceipt::factory()->create(['donor_id' => $donor->id]);

        $this->actingAs($donor)
            ->get(route('admin.receipts.show', $receipt))
            ->assertForbidden();
    }

    #[Test]
    public function admin_can_download_any_receipt_pdf(): void
    {
        Storage::fake('local');

        $admin = User::factory()->admin()->create();
        $file = UploadedFile::fake()->create('receipt.pdf', 10, 'application/pdf');
        $path = $file->store('receipts', 'local');

        $receipt = DonationReceipt::factory()->create(['pdf_path' => $path]);

        $this->actingAs($admin)
            ->get(route('admin.receipts.download', $receipt))
            ->assertOk();
    }
}
