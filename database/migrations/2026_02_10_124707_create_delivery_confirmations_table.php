<?php

// database/migrations/xxxx_xx_xx_create_delivery_confirmations_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('delivery_confirmations', function (Blueprint $table) {
            $table->id();

            $table->foreignId('glasses_id')->constrained('glasses')->cascadeOnDelete();
            $table->foreignId('conversation_id')->constrained('conversations')->cascadeOnDelete();

            $table->foreignId('donor_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('recipient_id')->constrained('users')->cascadeOnDelete();

            // الحالة: pending / received / not_received
            $table->enum('status', ['pending', 'received', 'not_received'])->default('pending');

            // ملاحظات الطرفين (اختياري)
            $table->text('donor_note')->nullable();
            $table->text('recipient_note')->nullable();

            $table->timestamp('recipient_responded_at')->nullable();

            $table->timestamps();

            // يمنع تكرار طلب تأكيد لنفس المحادثة (اختياري لكنه ممتاز)
            $table->unique('conversation_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('delivery_confirmations');
    }
};