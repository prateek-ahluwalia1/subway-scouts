<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobRosterAction extends Model
{
    use HasFactory;

    public function Admin(){
        return $this->belongsTo(User::class, 'action_by', 'id')->select('id','name');
    }

    
}
