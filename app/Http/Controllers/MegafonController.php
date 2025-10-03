<?php

namespace App\Http\Controllers;

use App\Http\Requests\Megafon\HistoryRequest;
use App\Models\Call;
use App\Services\FileStorageService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class MegafonController extends Controller
{
    // Карта команд → [FormRequest, метод]
    protected $handlers = [
        'history' => [HistoryRequest::class, 'history'],
    ];

    /**
     * Единая точка входа для команд Megafon.
     * Принимает Request и FileStorageService,
     * валидирует данные через соответствующий FormRequest,
     * вызывает соответствующий метод.
     *
     * @param Request $request
     * @param FileStorageService $fileStorageService
     * @return \Illuminate\Http\JsonResponse
     */
    public function cmd(Request $request, FileStorageService $fileStorageService)
    {
        // Точка входа, где определяется обработчик команды
        $cmd = $request->input('cmd');

        // Проверка на неизвестную команду
        if (!isset($this->handlers[$cmd])) {
            return response()->json([
                'message' => 'Неизвестная команда',
            ], 400);
        }

        [$formRequestClass, $method] = $this->handlers[$cmd];

        // Подгружается нужный FormRequest и валидируются данные
        $formRequest = app($formRequestClass);
        $data = $formRequest->setContainer(app())
                            ->setRedirector(app('redirect'))
                            ->validateResolved();

        $data = $formRequest->validated();

        // Вызов нужного обработчика
        return $this->$method($data, $fileStorageService);
    }

    /**
     * Обработчик команды `history`.
     * Принимает валидированные данные и сервис для сохранения файлов,
     * создает запись звонка и возвращает JSON-ответ.
     *
     * @param array $data
     * @param FileStorageService $fileStorageService
     * @return \Illuminate\Http\JsonResponse
     */
    public function history(array $data, FileStorageService $fileStorageService)
    {
        // Конвертация даты из формата Megafon в объект Carbon
        $data['datetime'] = Carbon::createFromFormat('Ymd\THis\Z', $data['datetime'], 'UTC');

        // Создание записи звонка в базе данных
        $call = Call::create($data);

        return response()->json([
            'message' => 'Звонок от мегафон успешно сохранен!',
            'call'    => $call,
        ], 201);
    }

}
