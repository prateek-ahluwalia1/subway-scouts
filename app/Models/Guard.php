<?php

namespace App\Models;

class Guard extends BaseModel {

    protected $table = 'guards';

    public function jobRoster() {
        return $this->hasMany('App\Models\JobNewRoster');
    }
}
