<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Http\Request;

class Guard extends Authenticatable implements JWTSubject
{
    protected $table="guards";
    protected $guarded = [];
    use HasFactory;

    const ACTIVE_STATUS = 'active';
    const INACTIVE_STATUS = 'inactive';
    const DELETED_STATUS = 'deleted';


    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return ['role' => 'guard'];
    }

    public function guardJobRoster()
    {
        return $this->hasMany(JobRoster::class, 'guard_id', 'id');
    }

    public function RunSheetJobRoster()
    {
        return $this->hasMany(RunSheetJobRoster::class, 'guard_id', 'id');
    }

    public function guardDocuments()
    {
        return $this->hasMany(GuardDocument::class, 'guard_id', 'id');
    }

    public function guardExternalIds()
    {
        return $this->hasMany(GuardInternalAndExternalIds::class, 'guard_id', 'id');
    }


    public function guardFeedback()
    {
        return $this->hasMany(GuardFeedBack::class, 'guard_id', 'id');
    }

    public function documents()
    {
        return $this->hasMany(GuardDocument::class, 'guard_id', 'id');
    }

    public function SiteGuard()
    {
        return $this->hasOne(SiteGuard::class, 'guard_id', 'id');
    }

    public function empDetails()
    {
        return $this->hasOne(GuardWorkDetail::class, 'guard_id', 'id');
    }

    public function contractorDetail()
    {
        return $this->hasOne(StaffContractorDetail::class, 'guard_id', 'id');
    }

    public function guardUniForm()
    {
        return $this->hasOne(StaffUniform::class, 'guard_id', 'id');
    }

    public function guardEmergencyContact()
    {
        return $this->hasOne(GuardWorkDetail::class, 'guard_id', 'id');
    }
    public function xeroMap()
    {
        return $this->hasOne(XeroEmployeeMap::class, 'crm_employee_id', 'id');
    }
    public function workDetail()
    {
        return $this->hasOne(GuardWorkDetail::class, 'guard_id', 'id');
    }



    

    





    

    
}
