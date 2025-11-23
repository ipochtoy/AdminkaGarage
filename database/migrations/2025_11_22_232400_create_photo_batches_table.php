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
        Schema::create('photo_batches', function (Blueprint $table) {
            $table->id();
            $table->string('correlation_id', 32)->unique();
            $table->bigInteger('chat_id')->index();
            $table->json('message_ids')->nullable();
            $table->timestamp('uploaded_at')->useCurrent();
            $table->timestamp('processed_at')->nullable();
            $table->enum('status', ['pending', 'processed', 'failed'])->default('pending');

            // Product description
            $table->string('title', 500)->default('');
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2)->nullable();
            $table->enum('condition', ['new', 'used', 'refurbished'])->nullable();
            $table->string('category', 200)->default('');
            $table->string('brand', 200)->default('');
            $table->string('size', 100)->default('');
            $table->string('color', 100)->default('');
            $table->string('sku', 200)->default('');
            $table->integer('quantity')->default(1);
            $table->text('ai_summary')->nullable();
            $table->json('locations')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('photo_batches');
    }
};
