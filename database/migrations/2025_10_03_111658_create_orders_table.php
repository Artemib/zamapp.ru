<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Список всех заказов
     */
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id(); // Уникальный ID заказа в CRM
            $table->timestamps(); // Дата и время создания заказа

            // Главный звонок, из которого создан заказ (1:1 связь с calls.id)
            $table->foreignId('main_call_id')
                ->nullable()
                ->constrained('calls')
                ->nullOnDelete();

            $table->string('status')->default('work');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
