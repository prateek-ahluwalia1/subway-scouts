<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TicketDetails extends Model
{
    use HasFactory;

    public function guardDetails(){
        return $this->belongsTo(Guard::class, 'guard_id')->select('id','first_name');
    }
    public function admin(){
        return $this->belongsTo(User::class, 'admin_id')->select('id','name');
    }
}
