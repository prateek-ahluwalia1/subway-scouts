<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RunSheetJobRoster extends Model
{
    use HasFactory;
    use SoftDeletes;
    
    protected $table = 'run_sheet_job_rosters';

    public function RunSheetJobRosterTask()
    {
        return $this->hasMany(RunSheetJobRosterTask::class, 'run_sheet_job_roster_id', 'id');
    }

    public function Runsheet()
    {
        return $this->belongsTo(RunSheet::class, 'run_sheet_id', 'id');
    }

    public function guardz()
    {
        return $this->belongsTo(Guard::class, 'guard_id', 'id');
    }


}
