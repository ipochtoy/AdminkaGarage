<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('photo_batches', function (Blueprint $table) {
            $table->enum('pochtoy_status', ['pending', 'success', 'failed'])->default('pending')->after('status');
            $table->string('pochtoy_error')->nullable()->after('pochtoy_status');
        });
    }

    public function down(): void
    {
        Schema::table('photo_batches', function (Blueprint $table) {
            $table->dropColumn(['pochtoy_status', 'pochtoy_error']);
        });
    }
};
