<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class XeroTimesheetQueue extends Model
{
    protected $table = 'xero_timesheet_queue';
    protected $guarded = [];
    protected $casts = ['employees' => 'array'];
}