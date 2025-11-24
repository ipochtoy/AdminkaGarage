<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_listings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('photo_batch_id')->constrained()->onDelete('cascade');

            // Platform info
            $table->string('platform'); // pochtoy, ebay, shopify
            $table->string('external_id')->nullable(); // ID on the platform
            $table->string('external_url')->nullable(); // URL on the platform

            // Status tracking
            $table->enum('status', ['pending', 'published', 'failed', 'deleted', 'sold'])->default('pending');
            $table->text('error_message')->nullable();

            // Platform-specific data
            $table->json('platform_data')->nullable(); // Store any platform-specific response

            // Price tracking (can differ per platform)
            $table->decimal('listed_price', 10, 2)->nullable();
            $table->string('currency', 3)->default('USD');

            // Timestamps
            $table->timestamp('published_at')->nullable();
            $table->timestamp('sold_at')->nullable();
            $table->timestamps();

            // Indexes
            $table->index(['platform', 'status']);
            $table->index('external_id');
            $table->unique(['photo_batch_id', 'platform']); // One listing per platform per batch
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_listings');
    }
};
