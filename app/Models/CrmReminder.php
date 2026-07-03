<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CrmReminder extends Model
{
    use HasFactory;

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by', 'id')->select('id','email','name');
    }
    public function notifyAlso()
    {
        return $this->belongsTo(User::class, 'notify_too', 'id')->select('id','email','name');
    }

}
