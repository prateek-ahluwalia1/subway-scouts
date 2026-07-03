<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BuildInFormHistory extends Model
{
    protected $table = "build_in_form_history";
    use HasFactory;
    public function GuardDetails()
    {
        return $this->belongsTo(Guard::class, 'guard_id')->select('id', 'name', 'phone');
    }
}
