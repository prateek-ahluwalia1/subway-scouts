<?php

namespace App\Models;

class Contractor extends BaseModel {

    protected $table = 'contractors';

    public function job() {
        return $this->belongsTo('App\Models\Job');
    }

    public function jobs() {
        return $this->hasMany('App\Models\Job');
    }
}
