<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GuardFeedBack extends Model
{
    use HasFactory;
    protected $table = 'guard_feed_backs';



public function admin()
{
    return $this->belongsTo(User::class, 'admin_id', 'id');
}

}
