<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GuardInternalAndExternalIds extends Model
{
    use HasFactory;

    protected $table = 'guard_external_ids';


    public function customer()
    {
        return $this->hasOne(Customer::class, 'id', 'customer_id');
    }
}
