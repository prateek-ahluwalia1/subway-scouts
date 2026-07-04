<?php

namespace App\Repositories;

use App\Models\UserActivity;

class UserActivityRepository extends BaseRepository {

    public function __construct() {
        parent::__construct();
        $this->model = new UserActivity();
    }


}
