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
            $table->foreignId('user_id')->constrained()->cascadeOnDelete(); 
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('condition', ['new','used']);
            
            $table->enum('status', ['available','in_contact','pending_donation','donated','reserved'])->default('available');
            $table->string('serial_number')->nullable()->unique();

             $table->string('brand')->nullable();
            $table->string('lens_type')->nullable(); // single_vision, bifocal...
            $table->string('vision_type')->nullable();

            $table->string('sph')->nullable();
            $table->string('cyl')->nullable();
            $table->string('axis')->nullable();
            $table->string('pd')->nullable();
            $table->string('prescription_note')->nullable();

            $table->string('frame_size')->nullable();  // small/medium/large/unknown
            $table->string('frame_color')->nullable();
            $table->string('age_group')->nullable();   // adult/kids...
            $table->string('gender')->nullable();      // male/female/unisex...

            $table->string('pickup_city')->nullable();
            $table->string('contact_method')->nullable(); // chat_only/phone/both

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
