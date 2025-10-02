<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class FileStorageService
{

    /**
     * Статический шорткат для вызова.
     * Удобно использовать, когда не нужен DI.
     */
    public static function store(string $url, string $folder = 'files', ?string $proxy = null): ?string
    {
        return (new self())->saveFromUrl($url, $folder, $proxy);
    }


    /**
     * Основной метод: скачивает файл по URL и сохраняет в указанную папку.
     *
     * Логика:
     *  1. Если явно указан $proxy — качаем через него.
     *  2. Если в .env установлен FILE_FETCH_FORCE_PROXY=true — качаем через прокси из .env.
     *  3. Иначе качаем напрямую.
     *
     * @param string $url    URL источника
     * @param string $folder Папка для сохранения (по умолчанию 'files')
     * @param string|null $proxy Явный URL прокси (например http://host:3000/proxy-get),
     *                           если null — используется логика по умолчанию
     * @return string|null   Публичный URL сохранённого файла или null при ошибке
     */
    public function saveFromUrl(string $url, string $folder = 'files', ?string $proxy = null): ?string
    {
        if (empty($url)) {
            return null;
        }

        try {
            // Получаем ответ (напрямую или через прокси)
            $response = $this->getResponse($url, $proxy);

            // Проверяем успешность
            if (!$this->isSuccessful($response, $url)) {
                return null;
            }

            // Формируем имя файла
            $filename = $this->buildFileName($url, $response, $folder);

            // Сохраняем файл (в память или чанками)
            $this->storeFile($response, $filename);

            // Возвращаем публичный URL
            return Storage::disk('public')->url($filename);
        } catch (\Throwable $e) {
            Log::error('File download error: ' . $e->getMessage(), ['url' => $url]);
            return null;
        }
    }

    /**
     * Определяет, как именно получать файл.
     *
     * @param string $url   URL источника
     * @param string|null $proxy Явный прокси (приоритетный)
     * @return \Illuminate\Http\Client\Response|null
     */
    private function getResponse(string $url, ?string $proxy = null): ?\Illuminate\Http\Client\Response
    {
        // 1. Если указан явно в параметре → используем всегда
        if ($proxy) {
            return $this->fetchFile($url, $proxy);
        }

        // 2. Если в .env включён флаг FILE_FETCH_FORCE_PROXY
        $forceProxy = env('FILE_FETCH_FORCE_PROXY', false);
        if ($forceProxy === true || $forceProxy === 'true') {
            $configProxy = env('FILE_FETCH_PROXY');
            return $configProxy ? $this->fetchFile($url, $configProxy) : null;
        }

        // 3. Иначе качаем напрямую
        return $this->fetchFile($url);
    }

    /**
     * Делает HTTP-запрос к файлу (напрямую или через Node-прокси).
     *
     * @param string $url   Реальный URL файла
     * @param string|null $proxy URL прокси (например http://host:3000/proxy-get)
     * @return \Illuminate\Http\Client\Response|null
     */
    private function fetchFile(string $url, ?string $proxy = null): ?\Illuminate\Http\Client\Response
    {
        $options = [
            'read_timeout' => 60, // максимум 60 секунд на чтение ответа
        ];

        // Если указан прокси — подставляем ?url=...
        $requestUrl = $url;
        if ($proxy) {
            $requestUrl = rtrim($proxy, '/') . '?url=' . urlencode($url);
        }

        return Http::withOptions($options)
            ->timeout(60) // общий таймаут
            ->retry(2, 500) // 2 повторные попытки при ошибке
            ->withHeaders(['User-Agent' => 'CRM-FileFetcher/1.0'])
            ->get($requestUrl);
    }

    /**
     * Проверяет успешность ответа и логирует ошибки.
     *
     * @param \Illuminate\Http\Client\Response|null $response
     * @param string $url
     * @return bool
     */
    private function isSuccessful(?\Illuminate\Http\Client\Response $response, string $url): bool
    {
        if (!$response || !$response->successful()) {
            Log::error('FileStorageService: неуспешный ответ', [
                'url' => $url,
                'status' => $response?->status(),
            ]);
            return false;
        }
        return true;
    }

    /**
     * Формирует имя файла для сохранения.
     * Если имя уже занято — добавляет uniqid().
     *
     * @param string $url
     * @param \Illuminate\Http\Client\Response $response
     * @param string $folder
     * @return string
     */
    private function buildFileName(string $url, \Illuminate\Http\Client\Response $response, string $folder): string
    {
        $originalName = $this->resolveFileName($url, $response);
        $filename = $folder . '/' . $originalName;

        // Защита от перезаписи
        if (Storage::disk('public')->exists($filename)) {
            $filename = $folder . '/' . uniqid() . '_' . $originalName;
        }

        return $filename;
    }

    /**
     * Сохраняет файл в Storage.
     * Если размер меньше 5 МБ — загружаем целиком.
     * Если больше — читаем поток чанками.
     *
     * @param \Illuminate\Http\Client\Response $response
     * @param string $filename
     */
    private function storeFile(\Illuminate\Http\Client\Response $response, string $filename): void
    {
        $fileSize = (int)($response->header('Content-Length')[0] ?? 0);

        if ($fileSize > 0 && $fileSize < 5 * 1024 * 1024) {
            // Маленький файл — целиком в память
            $content = $response->body();
            Storage::disk('public')->put($filename, $content);
        } else {
            // Большой файл — читаем чанками
            $stream = fopen('php://temp', 'w+b');
            foreach ($response->toPsrResponse()->getBody() as $chunk) {
                fwrite($stream, $chunk);
            }
            rewind($stream);
            Storage::disk('public')->put($filename, $stream);
            fclose($stream);
        }
    }

    /**
     * Определяет имя файла:
     *  - из Content-Disposition (если есть)
     *  - иначе из URL
     *  - если ничего нет — генерирует uniqid()
     *
     * @param string $url
     * @param \Illuminate\Http\Client\Response $response
     * @return string
     */
    private function resolveFileName(string $url, \Illuminate\Http\Client\Response $response): string
    {
        $disposition = $response->header('Content-Disposition');
        if ($disposition && preg_match('/filename="?([^"]+)"?/i', $disposition, $matches)) {
            return $matches[1];
        }

        $pathInfo = pathinfo(parse_url($url, PHP_URL_PATH));
        return $pathInfo['basename'] ?? uniqid('file_');
    }
}