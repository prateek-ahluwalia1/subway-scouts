<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PatrollingReport extends Model
{
    use HasFactory;
    public function scanners()
    {
        return $this->hasMany(QrScanner::class,'patrolling_report_id', 'id');
    }
    public function roster()
    {
        return $this->belongsTo(JobNewRoster::class, 'roster_id');
    }
}
