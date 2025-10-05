<?php

return [
    /*
    |--------------------------------------------------------------------------
    | PBX API Configuration
    |--------------------------------------------------------------------------
    |
    | Настройки для подключения к API АТС
    |
    */

    'api' => [
        'base_url' => env('PBX_API_URL', 'https://7280019.megapbx.ru/crmapi/v1'),
        'token' => env('PBX_API_TOKEN', '98daf46c-1850-42ef-a40a-db7f29ff08b0'),
        'timeout' => env('PBX_API_TIMEOUT', 60),
        'retry_attempts' => env('PBX_API_RETRY_ATTEMPTS', 3),
        'retry_delay' => env('PBX_API_RETRY_DELAY', 1000),
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Settings
    |--------------------------------------------------------------------------
    |
    | Настройки по умолчанию для импорта
    |
    */

    'import' => [
        'default_source' => env('PBX_DEFAULT_SOURCE', 'PBX API'),
        'auto_retry_failed' => env('PBX_AUTO_RETRY_FAILED', true),
        'batch_size' => env('PBX_BATCH_SIZE', 100),
    ],

    /*
    |--------------------------------------------------------------------------
    | Field Mapping
    |--------------------------------------------------------------------------
    |
    | Маппинг полей из API в поля модели Call
    |
    */

    'field_mapping' => [
        'callid' => 'callid',
        'datetime' => 'datetime',
        'type' => 'type',
        'status' => 'status',
        'client_phone' => 'client_phone',
        'user_pbx' => 'user_pbx',
        'diversion_phone' => 'diversion_phone',
        'duration' => 'duration',
        'wait' => 'wait',
        'link_record_pbx' => 'link_record_pbx',
        'link_record_crm' => 'link_record_crm',
        'transcribation' => 'transcribation',
        'from_source_name' => 'from_source_name',
    ],

    /*
    |--------------------------------------------------------------------------
    | Status Mapping
    |--------------------------------------------------------------------------
    |
    | Маппинг статусов звонков из API в статусы системы
    |
    */

    'status_mapping' => [
        'answered' => 'success',
        'no_answer' => 'missed',
        'busy' => 'busy',
        'failed' => 'cancel',
        'cancelled' => 'cancel',
        'success' => 'success',
        'missed' => 'missed',
        'cancel' => 'cancel',
    ],

    /*
    |--------------------------------------------------------------------------
    | Type Mapping
    |--------------------------------------------------------------------------
    |
    | Маппинг типов звонков из API в типы системы
    |
    */

    'type_mapping' => [
        'incoming' => 'in',
        'outgoing' => 'out',
        'in' => 'in',
        'out' => 'out',
    ],
];
