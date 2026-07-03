<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RunSheet extends Model
{
    use HasFactory;
    protected $table = 'run_sheets';


    public function runSheetDetails(){

        return $this->hasMany(RunSheetDetail::class);
    }

    public function RunSheetJobRoster()
    {
        return $this->hasMany(RunSheetJobRoster::class, 'run_sheet_id', 'id');
    }

    public function customer()
    {
        return $this->hasOne(Customer::class, 'id', 'customer_id');
    }

    


}
