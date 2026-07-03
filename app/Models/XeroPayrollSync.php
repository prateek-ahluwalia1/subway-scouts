<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class XeroPayrollSync extends Model {
    protected $fillable = [
        'fortnight_start','fortnight_end','xero_payrun_id',
        'status','payload_sent','response_received','error_message','synced_at'
    ];
    protected $casts = [
        'fortnight_start'   => 'date',
        'fortnight_end'     => 'date',
        'payload_sent'      => 'array',
        'response_received' => 'array',
        'synced_at'         => 'datetime',
    ];
}