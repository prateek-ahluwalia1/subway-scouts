<?php

namespace App\Repositories;

use App\Models\Job;
use App\Models\JobNewRoster;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;


class JobRosterRepository extends BaseRepository {

    const DURATION_MONTH = 'month';
    const DURATION_WEEK = 'week';
    const DURATION_TODAY = 'today';

    public function __construct() {
        $this->model = new JobNewRoster();
    }

    public function getCurrentMonthJobsByGuard($gaurdId, $type = 'pending', $duration=self::DURATION_MONTH, $week = 0) {
        /*return $this->model->with(['job' => function ($query) {
            $query->with(['customer', 'contractor']);
        }, 'guards'])->where(['publish_status' => 1, 'job_status'=> $type])->paginate(self::DEFULT_PAGES);*/

        $model = $this->model
            ->with(['job' => function ($query) {
            $query->with(['customer', 'contractor']);
        }, 'guards', 'rosterActivity']);
            if ($week < 0) {
           $model->where(['publish_status' => 1, 'guard_id' => $gaurdId]);
                
            }else{
            $model->where(['publish_status' => 1, 'job_status'=> $type, 'guard_id' => $gaurdId]);
            }
            switch ($duration) {
                case self::DURATION_TODAY:
                    if ($type == 'confirmed') {
                    // $now = Carbon::today();
                    $now = date('Y-m-d H:i:s');
                    $start = strtotime($now) - (60*60*15);
                    if (date('d', time()) == date('d', $start)) {
                    $start = date('Y-m-d 00:00:00', time());
                    $end = date('Y-m-d 23:59:59', time());
                    $model->whereBetween('start', [$start, $end]);
                    // $model->whereDate('start', Carbon::today());
                    }else{
                    // $end = strtotime($now) + (60*60*24);
                    $start = date('Y-m-d 00:00:00', time());
                    $end = date('Y-m-d 23:59:59', time());
                    // $model->whereBetween('start', [$start, $end]);
                    // $model->orWhereBetween('temp_end', [$start, $end]);
                    $model->where(function ($query) use($start, $end){
                        $query->whereBetween('start', [$start, $end])
                              ->orWhereBetween('end', [$start, $end]);
                    });

                    }
                    // echo $end;
                    // exit();
                    }else{
                    $model->whereDate('start', Carbon::today());
                    }
                    break;
                case self::DURATION_WEEK:
                    if($type == 'completed'){
                    if($week == 0){
                    // $model->whereBetween('start', [Carbon::now()->startOfWeek(), ]);
                    $model->whereBetween('start', [Carbon::now()->startOfWeek(), Carbon::parse('next monday')->toDateString()]);
                    
                    }else{
                        $startofweek = Carbon::now()->startOfWeek();
                        $endofweek = Carbon::now()->endOfWeek();
                        $startofweek = strtotime($startofweek) + (60*60*24*7 * $week) + 3600;
                        $endofweek = strtotime($endofweek) + (60*60*24*7 * $week) + 3600;
                        $startofweek = date('Y-m-d 00:00:00', $startofweek);
                        $endofweek = date('Y-m-d 23:59:59', $endofweek);
                        $model->where('start', '>=', $startofweek);
                        $model->where('start', '<=', $endofweek);
                        
                        // $model->whereBetween('start', [$startofweek, $endofweek]);
                    }
                    }else{
                    if($week == 0){
                    $model->whereBetween('start', [Carbon::now(), Carbon::parse('next monday')->toDateString()]);
                    $model->whereDate('start', '!=', Carbon::now()->toDateString());
                    }else{
                        $startofweek = Carbon::now()->startOfWeek();
                        // $endofweek = Carbon::parse('next monday')->toDateString();
                        $endofweek = Carbon::now()->endOfWeek();
                        if ($week == 0) {
                        $startofweek = strtotime($startofweek);
                        }else{
                        $startofweek = strtotime($startofweek) + (60*60*24*7 * $week) + 3600;
                        }
                        if ($week > 0) {
                        $endofweek = strtotime($endofweek) + (60*60*24*7 * $week);
                        }else{
                        $endofweek = strtotime($endofweek) + (60*60*24*7 * $week) + 3600;
                        }
                        $startofweek = date('Y-m-d 00:00:00', $startofweek);
                        $endofweek = date('Y-m-d 23:59:59', $endofweek);
                        // $model->whereBetween('start', [ $startofweek, $endofweek]);
                        $model->where('start', '>=', $startofweek);
                        $model->where('start', '<=', $endofweek);
                        $model->whereDate('start', '!=', Carbon::now()->toDateString());
                    }
                    }
                    break;
                case self::DURATION_MONTH:
                    $model->whereMonth('start', date('m'));
                    $model->whereYear('start', date('Y'));
                    break;
            }
            $model->orderBy('start', 'asc');
            return $model->paginate(self::DEFULT_PAGES);
    }



    public function getJobs($gaurdId, $type = 'pending', $duration=self::DURATION_MONTH, $week = 0) {
        /*return $this->model->with(['job' => function ($query) {
            $query->with(['customer', 'contractor']);
        }, 'guards'])->where(['publish_status' => 1, 'job_status'=> $type])->paginate(self::DEFULT_PAGES);*/

        $model = $this->model
            ->with(['job' => function ($query) {
            $query->with(['customer', 'contractor']);
        }, 'guards'])
            ->where(['publish_status' => 1, 'guard_id' => $gaurdId, 'un_published_shift' => 0])
            ->where(function($que) use ($type){
                if ($type == 'incompleted') {
                    $que->orWhere('job_status', 'confirmed');
                    $que->orWhere('job_status', 'pending');
                }else{
                    $que->orWhere('job_status', $type);
                }
            });
            switch ($duration) {
                case self::DURATION_TODAY:
                    $model->whereDate('start', Carbon::today());
                    break;
                case self::DURATION_WEEK:
                    if($week == 0){
                    $model->whereBetween('start', [Carbon::now()->startOfWeek(), Carbon::parse('next monday')->toDateString()]);
                    }else{
                    $startofweek = Carbon::now()->startOfWeek();
                    $endofweek = Carbon::parse('next monday')->toDateString();
                    // $startofweek = strtotime($startofweek) + (60*60*24*7 * $week) + 3600;
                    // $days = 7*$week;
                    if ($week > 0) {
                    $startofweek = strtotime("+".$week." week", strtotime($startofweek));
                    }else{
                    $startofweek = strtotime("-".$week." week", strtotime($startofweek));
                    }
                    // $startofweek = strtotime($startofweek) + (60*60*24*7 * $week) + 3600;

                    if ($week > 0) {
                        // $endofweek = strtotime($endofweek) + (60*60*24*8 * $week);
                        $endofweek = strtotime("+".$week." week", strtotime($endofweek));
                        }else{
                        $endofweek = strtotime("-".$week." week", strtotime($endofweek));

                        }
                    $startofweek = date('Y-m-d H:i', $startofweek);
                    $endofweek = date('Y-m-d H:i', $endofweek);
                    $model->whereBetween('start', [$startofweek, $endofweek]);
                    }
                    break;
                case self::DURATION_MONTH:
                    $model->whereMonth('start', date('m'));
                    $model->whereYear('start', date('Y'));
                    break;
            }
            $model->orderBy('start', 'asc');
            return $model->paginate(self::DEFULT_PAGES);
    }

    public function updateJobStatus($id, $status) {
        if ($status == 'rejected') {
       $roster = DB::table('job_rosters')->where('id', $id)->first();
       $model = $this->model->where(['id' => $id])->update(['job_status' => $status, 'update_status' => 1, 'guard_id' => null, 'rejected_by' => $roster->guard_id, 'last_update' => time()]);
            
        }else{
       $model = $this->model->where(['id' => $id])->update(['job_status' => $status, 'update_status' => 1, 'last_update' => time()]);
        }
        return ($model) ? true : false;
    }

    public function getAsapJobs($guard_id) {
        /*return $this->model->with(['job' => function ($query) {
            $query->with(['customer', 'contractor']);
        }, 'guards'])->where(['publish_status' => 1, 'job_status'=> $type])->paginate(self::DEFULT_PAGES);*/
        $a = null;
        $b = '';
        $model = $this->model
            ->with(['job' => function ($query) {
            $query->with(['customer', 'contractor']);
        }, 'guards'])
        ->where('asap', '=', '1')->where(
            function ($query) use ($a, $b) {
            return $query->where('guard_id', '=', $a)
                  ->orWhere('guard_id', '=', $b);
            }
        );

        $model->whereDate('start', Carbon::today()); 
        $model->orderBy('start', 'asc');
        $modal1 =  $model->paginate(self::DEFULT_PAGES);
        $new_data = array();
        foreach ($modal1 as $key => $value) {
            $already = DB::table('asap_jobs_rejected')->where(['guard_id' => $guard_id, 'roster_id' => $value->id])->first();
            if (empty($already)) {
                $new_data[] = $value;
            }
        }
        return $new_data;
    }


    public function jobSpecificDetail($gaurdId, $roster_id) {
        /*return $this->model->with(['job' => function ($query) {
            $query->with(['customer', 'contractor']);
        }, 'guards'])->where(['publish_status' => 1, 'job_status'=> $type])->paginate(self::DEFULT_PAGES);*/

        $model = $this->model
            ->with(['job' => function ($query) {
            $query->with(['customer', 'contractor']);
        }, 'guards', 'rosterActivity'])
            ->where(['id' => $roster_id, 'guard_id' => $gaurdId]);
            $model->orderBy('start', 'asc');
            return $model->paginate(self::DEFULT_PAGES);
    }

}
