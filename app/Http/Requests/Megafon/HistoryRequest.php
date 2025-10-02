<?php

namespace App\Http\Requests\Megafon;

use App\Enums\CallConstants;
use App\Http\Requests\Api\CallRequest;
use Illuminate\Foundation\Http\FormRequest;

class HistoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return (new CallRequest())->rules(); // используем те же правила, что и в базовом запросе
    }


    protected function prepareForValidation(): void
    {
        $status = $this->input('status');
        $type   = $this->input('type');

        $this->merge([
            'callid'          => $this->input('callid'),
            'datetime'        => $this->input('start'),
            'type'  => is_string($type) ? strtolower($type) : $type,
            'status' => is_string($status)
                ? (in_array($status, CallConstants::statusKeys(), true) ? $status : camel_to_snake($status))
                : $status,
            'client_phone'    => normalize_phone($this->input('phone')),
            'user_pbx'        => $this->input('user'),
            'diversion_phone' => normalize_phone($this->input('diversion')),
            'duration'        => $this->input('duration') ?? 0,
            'wait'            => $this->input('wait') ?? 0,
            'link_record_pbx' => $this->input('link') ?? null,
            'from_source_name' => 'megafon'
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