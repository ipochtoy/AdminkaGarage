<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('photo_batch_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2)->nullable();
            $table->string('brand')->nullable();
            $table->string('category')->nullable();
            $table->string('size')->nullable();
            $table->string('color')->nullable();
            $table->string('material')->nullable();
            $table->string('condition')->default('used');
            $table->string('status')->default('draft'); // draft, published
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
