<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GuardWorkDetail extends Model
{
    use HasFactory;

    protected $guarded = [];


    public function inductions()
    {
        return $this->hasMany(GuardInduction::class, 'guard_id', 'guard_id');
    }

    public function payrate()
    {
        return $this->hasOne(Payrate::class, 'payrate', 'id');
    }


    public function guardz()
    {
        return $this->belongsTo(Guard::class, 'guard_id', 'id');
    }
}
