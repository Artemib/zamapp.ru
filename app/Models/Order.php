<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Order extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'order_datetime',
        'city',
        'address',
        'phone',
        'additional_info',
        'main_call_id',
    ];

    protected $casts = [
        'order_datetime' => 'datetime',
    ];

    protected $appends = [
        'order_datetime_formatted',
    ];

    /**
     * Главный звонок, после которого сформирован заказ
     */
    public function mainCall(): BelongsTo
    {
        return $this->belongsTo(Call::class, 'main_call_id');
    }

    /**
     * Все звонки, связанные с заказом
     */
    public function calls(): BelongsToMany
    {
        return $this->belongsToMany(Call::class, 'order_calls');
    }

    /**
     * Форматированная дата и время заказа
     */
    public function getOrderDatetimeFormattedAttribute(): ?string
    {
        if (empty($this->order_datetime)) {
            return null;
        }
        
        return $this->order_datetime->format('d.m.Y H:i');
    }

    /**
     * Слияние заказов
     */
    public function mergeWith(Order $otherOrder, array $mergeFields = []): void
    {
        \Log::info('Starting merge process', [
            'source_order_id' => $this->id,
            'target_order_id' => $otherOrder->id,
            'merge_fields' => $mergeFields,
            'source_order_data' => $this->toArray(),
            'target_order_data' => $otherOrder->toArray()
        ]);
        
        // Переносим звонки из другого заказа
        $otherCalls = $otherOrder->calls;
        \Log::info('Transferring calls', [
            'calls_count' => $otherCalls->count(),
            'call_ids' => $otherCalls->pluck('id')->toArray()
        ]);
        
        foreach ($otherCalls as $call) {
            // Проверяем, не привязан ли уже этот звонок к текущему заказу
            if (!$this->calls()->where('call_id', $call->id)->exists()) {
                $this->calls()->attach($call->id);
            }
        }
        
        // Обновляем информацию согласно выбранным полям
        $updateData = [];
        
        // Если поля не выбраны, используем старую логику (переносим только пустые поля)
        if (empty($mergeFields)) {
            if (empty($this->city) && !empty($otherOrder->city)) {
                $updateData['city'] = $otherOrder->city;
            }
            
            if (empty($this->address) && !empty($otherOrder->address)) {
                $updateData['address'] = $otherOrder->address;
            }
            
            if (empty($this->phone) && !empty($otherOrder->phone)) {
                $updateData['phone'] = $otherOrder->phone;
            }
            
            if (empty($this->additional_info) && !empty($otherOrder->additional_info)) {
                $updateData['additional_info'] = $otherOrder->additional_info;
            }
        } else {
            // Переносим только выбранные поля
            foreach ($mergeFields as $field) {
                if (in_array($field, ['city', 'address', 'phone', 'additional_info']) && !empty($otherOrder->$field)) {
                    $updateData[$field] = $otherOrder->$field;
                }
            }
        }
        
        if (!empty($updateData)) {
            \Log::info('Updating source order with data from target', $updateData);
            $this->update($updateData);
        }
        
        // Мягко удаляем другой заказ
        \Log::info('Soft deleting target order', ['order_id' => $otherOrder->id]);
        $otherOrder->delete();
        
        \Log::info('Merge completed successfully', [
            'final_source_order' => $this->fresh()->toArray()
        ]);
    }
}
