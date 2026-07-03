<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Contractor extends Authenticatable implements JWTSubject
{
    use HasFactory;

    protected $table="contractors";

    const ACTIVE_STATUS = 'active';
    const INACTIVE_STATUS = 'inactive';
    const DELETED_STATUS = 'deleted';


    public function sites()
    {
        return $this->hasMany(Site::class, 'id', 'contractor_id');
    }

    public function otherDocuments()
    {
        return $this->hasMany(ContractorDocument::class, 'contractor_id', 'id');
    }

    public function moreContacts()
    {
        return $this->hasMany(ContractorMoreContacts::class, 'contractor_id', 'id');
    }
    

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
        return ['role' => 'Contractor'];
    }

    
}
