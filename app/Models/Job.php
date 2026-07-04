<?php

namespace App\Models;

class Job extends BaseModel {

    protected $table = 'sites';

    public function jobRoster() {
        return $this->hasMany('App\Models\JobNewRoster', 'site_id');
    }

    public function customer() {
        return $this->belongsTo('App\Models\Customer');
    }
    public function contractor() {
        return $this->belongsTo('App\Models\Contractor');
    }
}
