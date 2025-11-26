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
            $table->string('declaration_hs_code')->nullable()->after('declaration_ru');
            $table->string('declaration_sku')->nullable()->after('declaration_hs_code');
            $table->string('declaration_url')->nullable()->after('declaration_sku');
            $table->boolean('declaration_has_battery')->default(false)->after('declaration_url');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('photo_batches', function (Blueprint $table) {
            $table->dropColumn([
                'declaration_hs_code',
                'declaration_sku',
                'declaration_url',
                'declaration_has_battery',
            ]);
        });
    }
};
