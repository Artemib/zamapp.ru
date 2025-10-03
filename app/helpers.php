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