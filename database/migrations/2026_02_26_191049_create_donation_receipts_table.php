<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('donation_receipts', function (Blueprint $table) {
            $table->id();

            $table->foreignId('donation_request_id')->constrained()->cascadeOnDelete();
            $table->foreignId('glasses_id')->constrained('glasses')->cascadeOnDelete();
            $table->foreignId('donor_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('recipient_id')->constrained('users')->cascadeOnDelete();

            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->date('delivered_date')->nullable();
            $table->text('admin_note')->nullable();

            $table->string('receipt_code')->unique();     // مثل RCPT-XXXX
            $table->string('pdf_path')->nullable();       // storage path
            $table->timestamp('issued_at')->nullable();   // وقت إصدار الإيصال

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('donation_receipts');
    }
};
