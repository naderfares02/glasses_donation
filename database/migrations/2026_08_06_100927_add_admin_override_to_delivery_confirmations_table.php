<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('delivery_confirmations', function (Blueprint $table) {
            // من قام بتجاوز حالة "لم يستلم" وتحويلها يدوياً إلى "استلم"
            $table->foreignId('overridden_by')->nullable()->after('recipient_responded_at')
                ->constrained('users')->nullOnDelete();

            // السبب الإلزامي للتجاوز (توثيق/دليل القرار)
            $table->text('override_reason')->nullable()->after('overridden_by');

            $table->timestamp('overridden_at')->nullable()->after('override_reason');
        });
    }

    public function down(): void
    {
        Schema::table('delivery_confirmations', function (Blueprint $table) {
            $table->dropConstrainedForeignId('overridden_by');
            $table->dropColumn(['override_reason', 'overridden_at']);
        });
    }
};
