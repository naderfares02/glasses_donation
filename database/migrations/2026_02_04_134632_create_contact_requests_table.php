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
        Schema::create('contact_requests', function (Blueprint $table) {

            $table->id();
            $table->foreignId('glasses_id')->constrained()->cascadeOnDelete();
            $table->foreignId('donor_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('recipient_id')->constrained('users')->cascadeOnDelete();

            // القيمة النهائية بعد دمج alter_status_in_contact_requests_table
            $table->enum('status', ['pending','accepted','on_hold','rejected','closed'])->default('pending');

            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('closed_at')->nullable();

            $table->timestamps();

            // من add_unique_pending_request_to_contact_requests
            // يمنع تكرار pending لنفس النظارة ونفس المستفيد
            $table->unique(['glasses_id', 'recipient_id', 'status'], 'cr_unique_glasses_recipient_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contact_requests');
    }
};
