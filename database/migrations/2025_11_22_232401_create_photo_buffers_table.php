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
        Schema::create('photo_buffers', function (Blueprint $table) {
            $table->id();
            $table->string('file_id', 255)->unique();
            $table->bigInteger('message_id');
            $table->bigInteger('chat_id');
            $table->string('image', 255);
            $table->timestamp('uploaded_at')->useCurrent();

            // Recognized data
            $table->string('gg_label', 50)->default('');
            $table->string('barcode', 200)->default('');

            // Grouping
            $table->integer('group_id')->nullable();
            $table->integer('group_order')->default(0);

            // Status
            $table->boolean('processed')->default(false);
            $table->boolean('sent_to_bot')->default(false);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('photo_buffers');
    }
};
