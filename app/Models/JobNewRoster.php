<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Lumen\Auth\Authorizable;
use Illuminate\Database\Eloquent\SoftDeletes;


class JobNewRoster extends BaseModel {
    protected $table = 'job_rosters';
    use SoftDeletes;

    protected $fillable = [
        'job_status',
    ];

    public function job() {
        return $this->belongsTo('App\Models\Job', 'site_id');
    }

    public function guards() {
        return $this->belongsTo('App\Models\Guard', 'guard_id');
    }
    public function rosterActivity()
    {
        return $this->hasOne('App\Models\JobRosterActivity', 'job_roster_id');
    }
}
