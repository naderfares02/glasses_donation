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
        Schema::table('glasses', function (Blueprint $table) {
            $table->foreign('active_contact_request_id')
                ->references('id')
                ->on('contact_requests')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('glasses', function (Blueprint $table) {
            $table->dropForeign(['active_contact_request_id']);
        });
    }
};
