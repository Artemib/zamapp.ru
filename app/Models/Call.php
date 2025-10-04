<?php

namespace App\Models;

use App\Enums\CallConstants;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Call extends Model
{

    use HasFactory;

    protected $fillable = [
        'callid',
        'datetime',
        'type',
        'status',
        'client_phone',
        'user_pbx',
        'diversion_phone',
        'duration',
        'wait',
        'link_record_pbx',
        'link_record_crm',
        'transcribation',
        'from_source_name',
    ];

    protected $appends = [
        'status_name',
        'type_name',
        'datetime_formatted',
    ];

    /**
     * Связь: звонок может быть "главным" в одном заказе.
     */
    public function mainOrder(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Order::class, 'main_call_id');
    }

    /**
     * Связь: звонок может быть связан с несколькими заказами (история).
     */
    public function orders()
    {
        return $this->belongsToMany(Order::class, 'call_order')
            ->withPivot('relation_type')
            ->withTimestamps();
    }


    public function getStatusNameAttribute(): string
    {
        if ($this->status === null || !array_key_exists($this->status, CallConstants::STATUSES)) {
            return 'Неизвестно';
        }
        
        return CallConstants::STATUSES[$this->status];
    }



    public function getTypeNameAttribute(): string
    {
        if ($this->type === null || !array_key_exists($this->type, CallConstants::TYPES)) {
            return 'Неизвестно';
        }
        
        return CallConstants::TYPES[$this->type];
    }

    public function getClientPhoneNameAttribute(): ?string
    {
        if (empty($this->client_phone)) {
            return null;
        }
        
        return $this->client_phone;
    }

    public function getDiversionPhoneNameAttribute(): ?string
    {
        if (empty($this->diversion_phone)) {
            return null;
        }
        
        return $this->diversion_phone;
    }

    public function getDatetimeFormattedAttribute(): ?string
    {
        if (empty($this->datetime)) {
            return null;
        }
        
        return format_date_custom($this->datetime, true, 'd MMM HH:mm');
    }

}
