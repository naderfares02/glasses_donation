<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('complaint_messages', function (Blueprint $table) {
            $table->id();

            $table->foreignId('complaint_id')->constrained('complaints')->cascadeOnDelete();

            $table->foreignId('sender_id')->constrained('users')->cascadeOnDelete();
            $table->enum('sender_role', ['user','admin'])->default('user');

            $table->text('body');

            $table->timestamp('read_at')->nullable(); 

            $table->timestamps();

            $table->index(['complaint_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('complaint_messages');
    }
};
