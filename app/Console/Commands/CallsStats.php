<?php

namespace App\Console\Commands;

use App\Models\Call;
use Illuminate\Console\Command;
use Carbon\Carbon;

class CallsStats extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'calls:stats|cs {--period= : Период для статистики (today, week, month, year)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Показать статистику по звонкам';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $period = $this->option('period') ?? 'month';
        
        $this->info("📊 Статистика звонков за {$period}");
        $this->newLine();

        // Определяем период
        $dateFrom = $this->getDateFrom($period);
        $dateTo = Carbon::now();

        $this->info("📅 Период: {$dateFrom->format('d.m.Y H:i')} - {$dateTo->format('d.m.Y H:i')}");
        $this->newLine();

        // Общая статистика
        $totalCalls = Call::whereBetween('datetime', [$dateFrom, $dateTo])->count();
        $this->info("📞 Всего звонков: {$totalCalls}");

        if ($totalCalls === 0) {
            $this->warn('⚠️ Звонков за указанный период не найдено');
            return 0;
        }

        // Статистика по типам
        $incomingCalls = Call::whereBetween('datetime', [$dateFrom, $dateTo])
            ->where('type', 'in')->count();
        $outgoingCalls = Call::whereBetween('datetime', [$dateFrom, $dateTo])
            ->where('type', 'out')->count();

        $this->info("📥 Входящие: {$incomingCalls} (" . round($incomingCalls / $totalCalls * 100, 1) . "%)");
        $this->info("📤 Исходящие: {$outgoingCalls} (" . round($outgoingCalls / $totalCalls * 100, 1) . "%)");

        // Статистика по статусам
        $this->newLine();
        $this->info("📈 Статистика по статусам:");
        
        $statuses = Call::whereBetween('datetime', [$dateFrom, $dateTo])
            ->selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->get();

        foreach ($statuses as $status) {
            $percentage = round($status->count / $totalCalls * 100, 1);
            $statusName = $this->getStatusName($status->status);
            $this->line("   {$statusName}: {$status->count} ({$percentage}%)");
        }

        // Статистика по пользователям
        $this->newLine();
        $this->info("👥 Статистика по пользователям:");
        
        $users = Call::whereBetween('datetime', [$dateFrom, $dateTo])
            ->selectRaw('user_pbx, count(*) as count')
            ->groupBy('user_pbx')
            ->orderByDesc('count')
            ->get();

        foreach ($users as $user) {
            $percentage = round($user->count / $totalCalls * 100, 1);
            $this->line("   {$user->user_pbx}: {$user->count} ({$percentage}%)");
        }

        // Средняя длительность
        $avgDuration = Call::whereBetween('datetime', [$dateFrom, $dateTo])
            ->where('duration', '>', 0)
            ->avg('duration');

        if ($avgDuration) {
            $this->newLine();
            $this->info("⏱️ Средняя длительность звонка: " . round($avgDuration) . " сек (" . round($avgDuration / 60, 1) . " мин)");
        }

        // Статистика по источникам
        $this->newLine();
        $this->info("🔗 Статистика по источникам:");
        
        $sources = Call::whereBetween('datetime', [$dateFrom, $dateTo])
            ->selectRaw('from_source_name, count(*) as count')
            ->groupBy('from_source_name')
            ->get();

        foreach ($sources as $source) {
            $percentage = round($source->count / $totalCalls * 100, 1);
            $this->line("   {$source->from_source_name}: {$source->count} ({$percentage}%)");
        }

        return 0;
    }

    /**
     * Получить дату начала периода
     *
     * @param string $period
     * @return Carbon
     */
    private function getDateFrom(string $period): Carbon
    {
        switch ($period) {
            case 'today':
                return Carbon::today();
            case 'week':
                return Carbon::now()->startOfWeek();
            case 'month':
                return Carbon::now()->startOfMonth();
            case 'year':
                return Carbon::now()->startOfYear();
            default:
                return Carbon::now()->startOfMonth();
        }
    }

    /**
     * Получить название статуса
     *
     * @param string $status
     * @return string
     */
    private function getStatusName(string $status): string
    {
        $statusNames = [
            'success' => 'Успешные',
            'missed' => 'Пропущенные',
            'cancel' => 'Отмененные',
            'busy' => 'Занято',
            'not_available' => 'Недоступен',
            'not_allowed' => 'Запрещено',
            'not_found' => 'Не найден',
        ];

        return $statusNames[$status] ?? $status;
    }
}
