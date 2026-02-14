<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('donation_requests', function (Blueprint $table) {
            $table->id();

            $table->foreignId('glasses_id')->constrained()->cascadeOnDelete();
            $table->foreignId('conversation_id')->nullable()->constrained()->nullOnDelete();

            $table->foreignId('donor_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('recipient_id')->constrained('users')->cascadeOnDelete();

            $table->enum('status', ['pending','approved','rejected'])->default('pending');

            $table->date('delivered_date')->nullable();
            $table->text('donor_note')->nullable();
            $table->text('admin_note')->nullable();

            $table->timestamp('reviewed_at')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();

            $table->unique(['glasses_id','status']); 
            // ملاحظة: هذه تجعل فقط "pending/approved/rejected" واحدة لكل نظارة لكل حالة
            // إذا سببت لك مشاكل لاحقًا، نعدّلها.
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('donation_requests');
    }
};
