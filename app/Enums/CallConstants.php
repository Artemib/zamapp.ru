<?php

namespace App\Enums;

class CallConstants
{

    public const STATUSES = [
        'success' => 'Успешный',
        'missed' => 'Пропущенный',
        'cancel' => 'Отменённый',
        'busy' => 'Занято',
        'not_available' => 'Недоступен',
        'not_allowed' => 'Запрещено',
        'not_found' => 'Не найден',
    ];

    public const TYPES = [
        'in' => 'Входящий',
        'out' => 'Исходящий',
    ];


    /**
     * Возвращает список всех ключей статусов (например: success, missed и т.д.)
     *
     * @return array
     */
    public static function statusKeys(): array
    {
        return array_keys(self::STATUSES);
    }

    /**
     * Возвращает список всех ключей типов звонков (например: in, out)
     *
     * @return array
     */
    public static function typeKeys(): array
    {
        return array_keys(self::TYPES);
    }
}