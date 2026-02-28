<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('complaints', function (Blueprint $table) {
            $table->id();

            $table->foreignId('conversation_id')->constrained()->cascadeOnDelete();

            $table->foreignId('glasses_id')
                ->nullable()
                ->constrained('glasses')
                ->nullOnDelete();

            $table->foreignId('reporter_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('reported_user_id')->constrained('users')->cascadeOnDelete();

            $table->string('reason', 50);
            $table->text('description')->nullable();

            $table->enum('status', ['open','reviewing','resolved','dismissed'])->default('open');

            $table->foreignId('handled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('resolution_note')->nullable();

            $table->timestamps();

            $table->index(['status']);
            $table->index(['conversation_id']);
            $table->index(['reporter_id']);
            $table->index(['reported_user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('complaints');
    }
};