<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Таблица связей контактов с заказами
     */
    public function up(): void
    {
        Schema::create('contact_order', function (Blueprint $table) {


            $table->id(); // Уникальный ID связи

            // Связь с заказом - ID заказа, к которому относится контакт
            $table->foreignId('order_id')
                ->constrained('orders')
                ->cascadeOnDelete();

            // ID контакта, связанного с заказом
            $table->foreignId('contact_id')
                ->constrained('contacts')
                ->cascadeOnDelete();

            // Флаг: основной ли контакт в рамках этого заказа
            $table->boolean('is_primary')->default(false);

            // Описание роли контакта ("жена", "рабочий", "Telegram" и т.п.)
            $table->string('comment')->nullable();

            $table->timestamps();

            // Защита от дублирования: один и тот же контакт не может быть дважды в одном заказе
            $table->unique(['order_id', 'contact_id'], 'contact_order_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contact_order');
    }
};
