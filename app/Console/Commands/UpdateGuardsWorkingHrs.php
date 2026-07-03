<?php

namespace App\Console\Commands;

use App\Models\Guard;
use App\Models\GuardWorkDetail;
use Carbon\Carbon;
use DateInterval;
use DatePeriod;
use DateTime;
use Illuminate\Console\Command;

class UpdateGuardsWorkingHrs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'guard.UpdateGuardsWorkingHrs';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update Guards Working Hours Once in a Day.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $guards = Guard::with(['documents', 'empDetails'])
        ->where('guard_status', 'active')
        ->orderBy('first_name', 'asc')
        ->get();

        $currentDate = Carbon::now()->format('Y-m-d');
        $week_array = $this->calculateFutureMonthFourthnight($currentDate);
        $fortnight_start_date = new DateTime($week_array['week_start']);
        $fortnight_end_date = new DateTime($week_array['week_end']);
        $dates_periods = array();
        
        $period = new DatePeriod(
            new DateTime($fortnight_start_date->format("Y-m-d")),
            new DateInterval('P1D'),
            new DateTime($fortnight_end_date->format("Y-m-d"))
        );

        foreach ($period as $key => $value) {
            array_push($dates_periods, $value->format('Y-m-d'));
        }

        array_push($dates_periods, $fortnight_end_date->format("Y-m-d"));

        foreach ($guards as $guard) {
            if($guard->empDetails->guard_document_type == 'student_visa') {
                if($guard->empDetails->limit_exceed == 1) {
                    $guardStart = new DateTime($guard->empDetails->start_time);
                    $guardEnd = new DateTime($guard->empDetails->end_time);
                    $interval = new DateInterval('P1D');
                    $dateRange = new DatePeriod($guardStart, $interval, $guardEnd->modify('+1 day'));

                    $guardDates = [];
                    foreach ($dateRange as $date) {
                        $guardDates[] = $date->format('Y-m-d');
                    }
                    
                    $hasCompleteFortnight = false;
                    $guardDateCount = count($guardDates);

                    for ($i = 0; $i <= $guardDateCount - 14; $i++) {
                        $fourteenDays = array_slice($guardDates, $i, 14);
                        
                        $allExist = true;
                        foreach ($fourteenDays as $day) {
                            if (!in_array($day, $dates_periods)) {
                                $allExist = false;
                                break;
                            }
                        }
                        
                        if ($allExist) {
                            $hasCompleteFortnight = true;
                            break;
                        }
                    }

                    if ($hasCompleteFortnight) {
                        $totalWorkingHours = 72;    
                    } else {
                        $totalWorkingHours = 48;
                    }
                } else {
                    $totalWorkingHours = 48;
                }
            } else {
                if($guard->staff_type == 'part_time') {
                    $totalWorkingHours = 72;
                } else {
                    $totalWorkingHours = 76;
                }            
            }
            
            $guardWorkDetail = GuardWorkDetail::where('guard_id', $guard->id)->first();
            
            if ($guardWorkDetail) {
                $guardWorkDetail->weekly_work_hours_limitation = $totalWorkingHours;
                $guardWorkDetail->save();
            }
        }
    }

    function calculateFutureMonthFourthnight($givenDate)
    {
        $startDate = '2025-12-01';
        
        $date = $this->parseDateWithAutoDetection($givenDate);
        
        $formattedDate = $date->format('Y-m-d H:i:s');
        $endDate = $formattedDate;
        $startTime = strtotime($startDate);
        $endTime = strtotime($endDate);

        $secondsDiff = $endTime - $startTime;
        $daysDiff = floor($secondsDiff / (60 * 60 * 24));
        $totalFourthnight = floor($daysDiff/14);
        $totalFourthnight = $totalFourthnight * 14;
        $FourthnightStartDate = date('Y-m-d', strtotime($startDate . ' + '.$totalFourthnight.' days'));
        $FourthnightEndDate = date('Y-m-d', strtotime($FourthnightStartDate . ' + 13 days'));
        
        $ret['week_start'] = $FourthnightStartDate;
        $ret['week_end'] = $FourthnightEndDate;
        return $ret;
    }

    private function parseDateWithAutoDetection($dateString)
    {
        preg_match('/^(\d{1,2})-(\d{1,2})-(\d{4})/', $dateString, $matches);
        
        if (count($matches) !== 4) {
            return Carbon::parse($dateString);
        }
        
        $first = (int)$matches[1];
        $second = (int)$matches[2];
        $year = (int)$matches[3];
        
        if ($first > 12 && $first <= 31) {
            $date = Carbon::createFromFormat('d-m-Y H:i', $dateString);
        } elseif ($second > 12 && $second <= 31) {
            $date = Carbon::createFromFormat('m-d-Y H:i', $dateString);
        } else {
            $date = Carbon::createFromFormat('d-m-Y H:i', $dateString);
        }
        
        if ($date === false || !$date->isValid()) {
            return Carbon::parse($dateString);
        }
        
        return $date;
    }
}
