<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_listings', function (Blueprint $table) {
            // eBay specific fields
            $table->string('ebay_category_id')->nullable()->after('platform_data');
            $table->string('ebay_category_name')->nullable();
            $table->json('ebay_item_specifics')->nullable(); // Size, Brand, Color, etc.
            $table->string('ebay_condition_id')->nullable(); // 1000=New, 3000=Used, etc.
            $table->string('ebay_listing_format')->default('FIXED_PRICE'); // FIXED_PRICE, AUCTION
            $table->integer('ebay_quantity')->default(1);

            // Shopify specific fields
            $table->string('shopify_product_type')->nullable();
            $table->string('shopify_collection_id')->nullable();
            $table->json('shopify_tags')->nullable();
            $table->json('shopify_options')->nullable(); // Size, Color variants

            // Pochtoy specific fields
            $table->json('pochtoy_trackings')->nullable();

            // Override fields (platform-specific overrides)
            $table->string('override_title')->nullable();
            $table->text('override_description')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('product_listings', function (Blueprint $table) {
            $table->dropColumn([
                'ebay_category_id',
                'ebay_category_name',
                'ebay_item_specifics',
                'ebay_condition_id',
                'ebay_listing_format',
                'ebay_quantity',
                'shopify_product_type',
                'shopify_collection_id',
                'shopify_tags',
                'shopify_options',
                'pochtoy_trackings',
                'override_title',
                'override_description',
            ]);
        });
    }
};
