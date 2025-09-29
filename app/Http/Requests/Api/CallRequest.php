<?php

namespace App\Http\Requests\Api;

use App\Enums\CallConstants;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CallRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'callid' => ['required', 'string', 'unique:calls'], // Уникальный ID звонка в ВАТС
            'datetime' => ['required', 'date'], // Дата и время звонка
            'type' => ['required', Rule::in(CallConstants::typeKeys())], // Тип звонка
            'status' => ['required', Rule::in(CallConstants::statusKeys())], // Статус звонка
            'client_phone' => 'required', // Номер телефона клиента
            'user_pbx' => ['required', 'string'], // Идентификатор пользователя ВАТС (необходим для сопоставления на стороне CRM)
            'diversion_phone' => 'required', // Номер телефона ВАТС, через который прошел вызов
            'duration' => 'integer', // Общая длительность звонка в секундах
            'wait' => 'integer', // Время ожидания ответа
            'link_record_pbx' => ['nullable', 'url'], // Ссылка на запись разговора в ВАТС
            'link_record_crm' => ['nullable', 'url'], // Ссылка на запись разговора на стороне CRM
            'transcribation' => ['nullable', 'string'], // Расшифровка разговора аудио в текст
            'from_source_name' => ['required', 'string'], // Название источника откуда пришёл звонок в CRM
        ];
    }


    protected function prepareForValidation(): void
    {
        $type = $this->input('type');
        $status = $this->input('status');

        $this->merge([
            'type'  => is_string($type) ? strtolower($type) : $type,
            'status' => is_string($status)
                ? (in_array($status, CallConstants::statusKeys(), true) ? $status : camel_to_snake($status))
                : $status,
            'client_phone'    => normalize_phone($this->input('client_phone')),
            'diversion_phone' => normalize_phone($this->input('diversion_phone')),
            'duration' => $this->input('duration') ?? 0,
            'wait' => $this->input('wait') ?? 0,
        ]);
    }

    public function attributes(): array
    {
        return [
            'callid' => 'ID звонка',
            'datetime' => 'дата и время',
            'type' => 'тип звонка',
            'status' => 'статус звонка',
            'client_phone' => 'номер клиента',
            'user_pbx' => 'идентификатор пользователя ВАТС',
            'diversion_phone' => 'номер ВАТС',
            'duration' => 'длительность',
            'wait' => 'время ожидания',
            'link_record_pbx' => 'ссылка на запись в ВАТС',
            'link_record_crm' => 'ссылка на запись в CRM',
            'transcribation' => 'расшифровка разговора',
            'from_source_name' => 'источник звонка',
        ];
    }

}