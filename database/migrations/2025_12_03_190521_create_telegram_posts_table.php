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
        Schema::create('telegram_posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('telegram_channel_id')->constrained()->onDelete('cascade');
            $table->foreignId('photo_batch_id')->nullable()->constrained()->onDelete('set null');

            // Контент поста
            $table->string('title');
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2)->nullable();
            $table->string('currency', 10)->default('USD');
            $table->string('buy_link')->nullable();
            $table->json('images')->nullable();              // Пути к фото

            // Статус публикации
            $table->enum('status', ['draft', 'scheduled', 'sent', 'failed'])->default('draft');
            $table->timestamp('scheduled_at')->nullable();   // Для отложенной публикации
            $table->timestamp('sent_at')->nullable();
            $table->string('telegram_message_id')->nullable(); // ID сообщения в Telegram
            $table->text('error_message')->nullable();

            // Статус продажи
            $table->boolean('is_sold')->default(false);
            $table->timestamp('sold_at')->nullable();

            $table->timestamps();

            $table->index(['status', 'scheduled_at']);
            $table->index('photo_batch_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('telegram_posts');
    }
};
