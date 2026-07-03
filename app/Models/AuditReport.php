<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuditReport extends Model
{
    use HasFactory;
    public function guardDetails()
    {
        return $this->belongsTo(Guard::class, 'guard_id')->select('id', 'name');
    }
    public function site()
    {
        return $this->belongsTo(Site::class, 'site_id')->select('id', 'site_name');
    }
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id')->select('id', 'name');
    }
    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id')->select('id', 'name');
    }
}
