<?php

namespace Tests\Feature\Donor;

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
    public function donor_can_view_their_receipts_index(): void
    {
        $donor = User::factory()->donor()->create();
        DonationReceipt::factory()->create(['donor_id' => $donor->id]);

        $this->actingAs($donor)
            ->get(route('donor.receipts.index'))
            ->assertOk();
    }

    #[Test]
    public function donor_can_view_their_own_receipt(): void
    {
        $donor = User::factory()->donor()->create();
        $receipt = DonationReceipt::factory()->create(['donor_id' => $donor->id]);

        $this->actingAs($donor)
            ->get(route('donor.receipts.show', $receipt))
            ->assertOk();
    }

    #[Test]
    public function donor_cannot_view_another_donors_receipt(): void
    {
        $donor = User::factory()->donor()->create();
        $otherDonor = User::factory()->donor()->create();
        $receipt = DonationReceipt::factory()->create(['donor_id' => $otherDonor->id]);

        $this->actingAs($donor)
            ->get(route('donor.receipts.show', $receipt))
            ->assertForbidden();
    }

    #[Test]
    public function recipient_cannot_access_donor_receipts(): void
    {
        $recipient = User::factory()->recipient()->create();
        $receipt = DonationReceipt::factory()->create();

        $this->actingAs($recipient)
            ->get(route('donor.receipts.show', $receipt))
            ->assertForbidden();
    }

    #[Test]
    public function donor_can_download_their_receipt_pdf(): void
    {
        Storage::fake('public');

        $donor = User::factory()->donor()->create();
        $file = UploadedFile::fake()->create('receipt.pdf', 10, 'application/pdf');
        $path = $file->store('receipts', 'public');

        $receipt = DonationReceipt::factory()->create([
            'donor_id' => $donor->id,
            'pdf_path' => $path,
        ]);

        $this->actingAs($donor)
            ->get(route('donor.receipts.download', $receipt))
            ->assertOk();
    }

    #[Test]
    public function downloading_a_receipt_without_a_pdf_returns_404(): void
    {
        Storage::fake('public');

        $donor = User::factory()->donor()->create();
        $receipt = DonationReceipt::factory()->create([
            'donor_id' => $donor->id,
            'pdf_path' => null,
        ]);

        $this->actingAs($donor)
            ->get(route('donor.receipts.download', $receipt))
            ->assertNotFound();
    }
}
