<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('delivery_confirmations', function (Blueprint $table) {
            $table->foreignId('donation_request_id')
                ->after('id')
                ->constrained('donation_requests')
                ->cascadeOnDelete();

            // يمنع تكرار تأكيد لنفس الطلب
            $table->unique('donation_request_id');
        });
    }

    public function down(): void
    {
        Schema::table('delivery_confirmations', function (Blueprint $table) {
            $table->dropUnique(['donation_request_id']);
            $table->dropConstrainedForeignId('donation_request_id');
        });
    }
};