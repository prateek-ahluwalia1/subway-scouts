<?php

namespace App\Repositories;

use App\Models\Job;
use App\Models\JobNewRoster;

class JobRepository extends BaseRepository {

    public $jobRosterModel;
    public function __construct(JobRosterRepository $jobRosterRepository) {
        $this->model = new Job();
        $this->jobRosterModel = $jobRosterRepository;
    }

    public function getJobById($id, $guard_id) {
    	// print_r($guard_id);
    	// exit();
        // return $this->model->with(['jobRoster', 'customer', 'contractor'])->find($id);
        return $this->model->with(["jobRoster" => function($q) use($guard_id){
            $q->where('job_rosters.guard_id', '=', $guard_id);
        }, 'customer', 'contractor'])->find($id);
    }



}
