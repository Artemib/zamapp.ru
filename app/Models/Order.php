<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'main_call_id',
        'status',
    ];


    /**
     * Связь: главный звонок, из которого был создан заказ (1:1).
     */
    public function mainCall()
    {
        return $this->belongsTo(Call::class, 'main_call_id');
    }

    /**
     * Связь: все звонки, связанные с этим заказом (M:N).
     */
    public function calls()
    {
        return $this->belongsToMany(Call::class, 'call_order')
            ->withPivot('relation_type')
            ->withTimestamps();
    }

    /**
     * Связь: все контакты, участвующие в заказе (M:N).
     */
    public function contacts()
    {
        return $this->belongsToMany(Contact::class, 'contact_order')
            ->withPivot('is_primary', 'comment')
            ->withTimestamps();
    }

    /**
     * Получить основной контакт (флаг is_primary=true).
     */
    public function primaryContact()
    {
        return $this->belongsToMany(Contact::class, 'contact_order')
            ->wherePivot('is_primary', true)
            ->withPivot('comment')
            ->withTimestamps();
    }



}
