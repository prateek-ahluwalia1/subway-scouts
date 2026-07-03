<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\Hash;
use App\Models\GuardWorkDetail;
use App\Models\Guard;
use App\Models\Customer;
use App\Models\Site;
use Carbon\Carbon;
use \DateTime;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Models\GuardDocument;

class LocationImport implements ToModel, WithHeadingRow
{

    public function model(array $location)
    {
        if(!empty($location['site_name']))
        {
            $customer = Customer::where('name', $location['customer'])->first();
        
            $address = $location['address'] . ', ' . $location['city'] . ' ' . $location['state_province'] . ' ' . $location['zippostal_code'] . ', ' . $location['country'];
            $currentDate = Carbon::today()->format('Y-m-d');
            
            $site = new Site();
            $site->booking_id = substr(uniqid(), 0, 4).'-'.substr(uniqid(), 5, 4);
            if(isset($customer->id) && $customer->id != null)
            {
            $site->customer_id = $customer->id;
            }
            $site->site_name = $location['site_name'];
            $site->type = "metro";
            $site->site_description = "Static Guard";
            $site->state = $location['dropdown'];
            $site->address = $address;
            $site->site_status = "active";
            $site->job_instrcutions = $location['notes'];
            $site->start = $currentDate;

            $site->save();
        }

    }

}