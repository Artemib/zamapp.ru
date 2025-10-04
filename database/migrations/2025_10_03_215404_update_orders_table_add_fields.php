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
        Schema::table('orders', function (Blueprint $table) {
            // Добавляем поля для заказа
            $table->timestamp('order_datetime')->nullable()->comment('Дата и время оформления заказа');
            $table->string('city')->nullable()->comment('Город');
            $table->text('address')->nullable()->comment('Адрес');
            $table->string('phone')->nullable()->comment('Телефон');
            $table->text('additional_info')->nullable()->comment('Дополнительная информация');
            
            // Связь с главным звонком
            $table->foreignId('main_call_id')->nullable()->constrained('calls')->onDelete('set null')->comment('Главный звонок');
            
            // Soft delete
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropSoftDeletes();
            $table->dropForeign(['main_call_id']);
            $table->dropColumn([
                'order_datetime',
                'city', 
                'address',
                'phone',
                'additional_info',
                'main_call_id'
            ]);
        });
    }
};
