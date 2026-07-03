<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Guard;


class Near_misses extends Model
{
    use HasFactory;

    protected $table = 'monthly_near_misses';

    public function guardRelation()
    {
        return $this->belongsTo(Guard::class, 'staff_member', 'id');
    }
}
