<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RunSheetDetail extends Model
{
    use HasFactory;
    protected $table = 'run_sheet_details';

    
    public function runSheet()
    {
        return $this->belongsTo(RunSheet::class);
    }
}
