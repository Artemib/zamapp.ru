<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Список всех уникальных контактов
     */
    public function up(): void
    {
        Schema::create('contacts', function (Blueprint $table) {
            $table->id();

            // Тип контакта (телефон, телеграм, вотсап, почта и т.д.)
            $table->enum('type', ['phone', 'telegram', 'whatsapp', 'email', 'other']);

            // Значение контакта (номер, ник, адрес и т.п.)
            $table->string('value');

            // Источник появления контакта: auto - из звонка, manual - вручную, import - из файла
            $table->enum('source', ['auto', 'manual', 'import']);

            // Доп. описание ("жена", "рабочий", "Telegram")
            $table->string('label')->nullable();

            $table->timestamps();

            // Индекс для поиска по типу и значению — ускоряет выборку и предотвращает дубли
            $table->unique(['type', 'value'], 'contacts_type_value_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contacts');
    }
};
