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
        Schema::table('photo_batches', function (Blueprint $table) {
            $table->string('declaration_en')->nullable()->after('ai_summary');
            $table->string('declaration_ru')->nullable()->after('declaration_en');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('photo_batches', function (Blueprint $table) {
            $table->dropColumn(['declaration_en', 'declaration_ru']);
        });
    }
};
