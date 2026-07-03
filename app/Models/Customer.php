<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Customer extends Authenticatable implements JWTSubject
{
    use HasFactory;
    protected $table="customers";

    const ACTIVE_STATUS = 'active';
    const INACTIVE_STATUS = 'inactive';
    const DELETED_STATUS = 'deleted';

    public function sites()
    {
        return $this->hasMany(Site::class, 'id', 'customer_id');
    }

    public function otherDocuments()
    {
        return $this->hasMany(CustomerDocument::class, 'customer_id', 'id');
    }

    public function moreContacts()
    {
        return $this->hasMany(CustomerMoreContact::class, 'customer_id', 'id');
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
        return ['role' => 'Customer'];
    }
}
