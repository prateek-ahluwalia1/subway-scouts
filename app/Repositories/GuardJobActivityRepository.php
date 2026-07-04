<?php

namespace App\Repositories;

use App\Models\GuardJobActivity;

class GuardJobActivityRepository extends BaseRepository {

    public function __construct() {
        parent::__construct();
        $this->model = new GuardJobActivity();
    }
    
}
