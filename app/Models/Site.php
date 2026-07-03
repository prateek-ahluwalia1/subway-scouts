<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Site extends Model
{
    use HasFactory;
    protected $table = 'sites';


    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'id');
    }

    public function customerz()
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'id')->value('id');
    }

    public function jobRoster()
    {
        return $this->hasMany(JobRoster::class, 'site_id', 'id');
    }
    public function QRCodes()
    {
        return $this->hasMany(SiteQrCode::class, 'site_id', 'id');
    }

    public function SiteDeleteReason()
    {
        return $this->hasOne(DeleteSiteReason::class, 'site_id', 'id');
    }


}
