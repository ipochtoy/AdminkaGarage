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
            $table->string('ebay_title', 80)->nullable();
            $table->text('ebay_description')->nullable();
            $table->string('ebay_condition', 50)->nullable();
            $table->string('ebay_brand', 100)->nullable();
            $table->string('ebay_size', 50)->nullable();
            $table->string('ebay_color', 50)->nullable();
            $table->decimal('ebay_price', 10, 2)->nullable();
            $table->string('ebay_category', 50)->nullable();
            $table->json('ebay_tags')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('photo_batches', function (Blueprint $table) {
            $table->dropColumn([
                'ebay_title',
                'ebay_description',
                'ebay_condition',
                'ebay_brand',
                'ebay_size',
                'ebay_color',
                'ebay_price',
                'ebay_category',
                'ebay_tags',
            ]);
        });
    }
};
