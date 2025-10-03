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
