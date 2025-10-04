<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'value',
        'source',
        'label',
    ];

    /**
     * Связь: все заказы, в которых используется данный контакт (M:N).
     */
    public function orders()
    {
        return $this->belongsToMany(Order::class, 'contact_order')
            ->withPivot('is_primary', 'comment')
            ->withTimestamps();
    }

    /**
     * Лок scope: получить только телефонные контакты.
     */
    public function scopePhones($query)
    {
        return $query->where('type', 'phone');
    }

    /**
     * Лок scope: получить только мессенджеры.
     */
    public function scopeMessengers($query)
    {
        return $query->whereIn('type', ['telegram', 'whatsapp']);
    }

}
