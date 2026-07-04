<?php

namespace App\Repositories;

use App\Models\JobRosterActivity;

class JobRosterActivityRepository extends BaseRepository {

    public function __construct() {
        parent::__construct();
        $this->model = new JobRosterActivity();
    }


    /**
     * Update the model in the database.
     *
     * @param int $id
     * @param array $inputs
     */
    public function setStatusInactive(int $guardId, int $rosterId, array $inputs = ['status'=>0]) {
        try {
            $model = $this->model->where(['guard_id'=>$guardId, 'job_roster_id'=>$rosterId, 'status'=> 1])->update($inputs);
            return $model;
        } catch (QueryException $e) {
            return false;
        }
    }

}
