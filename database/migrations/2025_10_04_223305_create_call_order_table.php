<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * История всех звонков по каждому заказу.
     */
    public function up(): void
    {
        Schema::create('call_order', function (Blueprint $table) {

            $table->id(); // Уникальный ID связи

            // ID заказа, с которым связан звонок
            $table->foreignId('order_id')
                ->constrained('orders')
                ->cascadeOnDelete();

            // ID звонка, который связан с заказом
            $table->foreignId('call_id')
                ->constrained('calls')
                ->cascadeOnDelete();

            // Как связь была создана: main - главный, auto - автоматически, manual - вручную, callback - перезвон, extra - доп. звонок
            $table->enum('relation_type', [
                'main', 'auto', 'manual', 'callback', 'extra'
            ])->default('auto');

            $table->timestamps();

            // Не допускаем дублирование связей заказ ↔ звонок
            $table->unique(['order_id', 'call_id'], 'call_order_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('call_order');
    }
};
