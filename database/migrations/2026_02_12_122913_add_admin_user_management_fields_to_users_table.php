<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // حالة الحساب
            $table->enum('status', ['active', 'suspended'])->default('active')->after('role');

            // تعليق الحساب
            $table->timestamp('suspended_at')->nullable()->after('status');
            $table->unsignedBigInteger('suspended_by')->nullable()->after('suspended_at');
            $table->string('suspended_reason', 255)->nullable()->after('suspended_by');

            // من قام بتغيير الدور (للتوثيق)
            $table->unsignedBigInteger('role_changed_by')->nullable()->after('suspended_reason');
            $table->timestamp('role_changed_at')->nullable()->after('role_changed_by');

            // Soft Deletes
            $table->softDeletes();

            // FK (اختياري لكنه ممتاز)
            $table->foreign('suspended_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('role_changed_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['suspended_by']);
            $table->dropForeign(['role_changed_by']);

            $table->dropColumn([
                'status',
                'suspended_at',
                'suspended_by',
                'suspended_reason',
                'role_changed_by',
                'role_changed_at',
                'deleted_at',
            ]);
        });
    }
};