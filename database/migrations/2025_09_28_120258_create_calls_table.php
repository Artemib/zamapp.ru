<?php

use App\Enums\CallConstants;
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
        Schema::create('calls', function (Blueprint $table) {
            $table->id(); // ID звонка в CRM
            $table->string('callid')->unique(); // Уникальный ID звонка в ВАТС
            $table->timestamp('datetime'); // Дата и время звонка
            $table->enum('type', CallConstants::typeKeys());
            $table->enum('status', CallConstants::statusKeys());
            $table->string('client_phone'); // Номер телефона клиента
            $table->string('user_pbx'); // Идентификатор пользователя ВАТС (необходим для сопоставления на стороне CRM)
            $table->string('diversion_phone'); // Номер телефона ВАТС, через который прошел вызов
            $table->integer('duration'); // Общая длительность звонка в секундах
            $table->integer('wait'); // Время ожидания ответа
            $table->string('link_record_pbx')->nullable(); // Ссылка на запись разговора в ВАТС
            $table->string('link_record_crm')->nullable(); // Ссылка на запись разговора на стороне CRM
            $table->text('transcribation')->nullable(); // Расшифровка разговора аудио в текст
            $table->string('from_source_name'); // Название источника откуда пришёл звонок в CRM
            $table->timestamps(); // Дата и время создания записи о звонке в CRM
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('calls');
    }
};
