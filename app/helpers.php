<?php


if (! function_exists('camel_to_snake')) {
    /**
     * Преобразует строку из CamelCase / PascalCase в snake_case.
     *
     * Примеры:
     *   Success      → success
     *   NotAvailable → not_available
     *   NotFound     → not_found
     *
     * @param string|null $value
     * @return string|null
     */
    function camel_to_snake(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        return strtolower(preg_replace('/([a-z])([A-Z])/', '$1_$2', $value));
    }
}


if (!function_exists('normalize_phone')) {
    /**
     * Приводит номер телефона к формату 9XXXXXXXXX, исключая пробелы, скобки и т.д.
     */
    function normalize_phone(?string $phone): ?string
    {
        if (!$phone) {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $phone);

//        if (preg_match('/^81[12]/', $digits)) {
//            return $digits;
//        }
//
//        if (str_starts_with($digits, '8') || str_starts_with($digits, '7')) {
//            $digits = substr($digits, 1);
//        }

        return $digits;
    }
}

if (!function_exists('format_date_custom')) {
    /**
     * Форматирует дату в русском формате с заданной временной зоной
     * 
     * @param string|\DateTime|\Carbon\Carbon|null $date
     * @param bool $fullMonthName Использовать полное название месяца (по умолчанию: false - сокращенное)
     * @param string $format Формат даты (по умолчанию: 'd MMM yyyy HH:mm:ss')
     * @param string $timeZone Временная зона (по умолчанию: 'Europe/Moscow')
     * @return string|null
     */
    function format_date_custom($date, bool $fullMonthName = false,string $format = 'd MMM yyyy HH:mm:ss', string $timeZone = 'Europe/Moscow'): ?string
    {
        if (empty($date)) {
            return null;
        }
        
        // Конвертируем в заданную временную зону
        $carbonDate = \Carbon\Carbon::parse($date)->setTimezone($timeZone);
        
        // Автоматически определяем формат месяца (только если в формате есть MMM)
        if ($fullMonthName && strpos($format, 'MMM') !== false && strpos($format, 'MMMM') === false) {
            $format = str_replace('MMM', 'MMMM', $format);
        }
        
        // Используем IntlDateFormatter для русской локализации
        $formatter = new \IntlDateFormatter(
            'ru_RU',
            \IntlDateFormatter::NONE,
            \IntlDateFormatter::NONE,
            $timeZone,
            \IntlDateFormatter::GREGORIAN,
            $format
        );
        
        // Убираем точку после сокращения месяца (только для сокращенных названий)
        $formatted = $formatter->format($carbonDate->toDateTime());
        if (!$fullMonthName) {
            $formatted = str_replace('.', '', $formatted);
        }
        
        return $formatted;
    }
}
