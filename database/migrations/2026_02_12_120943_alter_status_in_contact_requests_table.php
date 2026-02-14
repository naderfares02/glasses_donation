<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        DB::statement("ALTER TABLE contact_requests MODIFY status ENUM('pending','accepted','on_hold','rejected','closed') NOT NULL DEFAULT 'pending'");
    }

    public function down()
    {
        DB::statement("ALTER TABLE contact_requests MODIFY status ENUM('pending','accepted','rejected','closed') NOT NULL DEFAULT 'pending'");
    }
};
