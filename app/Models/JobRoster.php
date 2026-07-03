<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Job;
use App\Models\Guard;
use App\Models\GreenCall;
use App\Models\WelfareCall;
use App\Models\JobRosterActivity;
use Illuminate\Database\Eloquent\SoftDeletes;

class JobRoster extends Model
{
    use HasFactory;
    use SoftDeletes;


    public function site()
    {
        return $this->belongsTo(Site::class, 'site_id', 'id');
    }

    public function guardz()
    {
        return $this->belongsTo(Guard::class, 'guard_id', 'id');
    }

    public function jobRosterTask()
    {
        return $this->hasMany(JobRosterTask::class, 'job_roster_id', 'id');
    }

    public function guardTimeSheetComments()
    {
        return $this->hasMany(GuardTimeSheetComment::class, 'g_id', 'guard_id');
    }

    public function greenCall()
    {
        return $this->hasMany(GreenCall::class, 'job_id', 'id');
    }


    public function rosterActivity()
    {
        return $this->hasOne(JobRosterActivity::class, 'job_roster_id', 'id');
    }


    function WelfareCall()
    {
        return $this->hasMany(WelfareCall::class, 'job_roster_id', 'id');
    }

    function activity()
    {
        return $this->hasOne(JobRosterActivity::class, 'job_roster_id', 'id');
    }

    function Guards()
    {
        return $this->hasOne(Guard::class, 'id', 'guard_id')->select('id', 'first_name', 'middle_name', 'last_name','phone');
    }
    function Sites()
    {
        return $this->hasOne(Site::class, 'id', 'site_id');
    }

    function newJobRoster()
    {
        return $this->belongsTo(JobNewRoster::class, 'roster_id', 'id');
    }

    function rosterPayrate()
    {
        return $this->hasOne(Payrate::class, 'payrate', 'id');
    }

    function rosterChargeRate()
    {
        return $this->hasOne(ChargeRate::class, 'chargerate', 'id');
    }
    function guardLeaveRequest()
    {
        return $this->hasMany(GuardLeave::class, 'roster_id', 'id');
    }




}
