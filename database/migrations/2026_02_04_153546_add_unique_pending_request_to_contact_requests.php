<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('contact_requests', function (Blueprint $table) {
            // يمنع تكرار pending لنفس النظارة ونفس المستفيد
            $table->unique(['glasses_id', 'recipient_id', 'status'], 'cr_unique_glasses_recipient_status');
        });
    }

    public function down(): void
    {
        Schema::table('contact_requests', function (Blueprint $table) {
            $table->dropUnique('cr_unique_glasses_recipient_status');
        });
    }
};

