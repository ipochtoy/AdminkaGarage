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
        Schema::create('telegram_channels', function (Blueprint $table) {
            $table->id();
            $table->string('name');                          // "Гараж 1", "Гараж 2"
            $table->string('bot_token');                     // Токен бота
            $table->string('chat_id');                       // ID канала для постинга
            $table->string('link_template');                 // https://garage1.pochtoy.com/item/{correlation_id}
            $table->string('site_name')->nullable();         // Название сайта для отображения
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('telegram_channels');
    }
};
