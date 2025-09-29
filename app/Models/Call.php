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
    ];


    public function getStatusNameAttribute(): string
    {
        return CallConstants::STATUSES[$this->status];
    }

    public function getTypeNameAttribute(): string
    {
        return CallConstants::TYPES[$this->type];
    }

}
