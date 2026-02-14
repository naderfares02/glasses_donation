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
        Schema::create('glasses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // المتبرع
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('lens_type')->nullable();
            $table->string('prescription')->nullable();
            $table->enum('condition', ['new','used']);
            $table->enum('status', ['available','in_contact','donated'])->default('available');

            $table->unsignedBigInteger('active_contact_request_id')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('glasses');
    }
};
