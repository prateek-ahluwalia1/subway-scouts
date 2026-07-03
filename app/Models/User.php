<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Database\Eloquent\Casts\Attribute;

class User extends Authenticatable implements JWTSubject
{
    protected $table = "users";
    
    use HasApiTokens, HasFactory, Notifiable;

    const ACTIVE_STATUS = 'active';
    const INACTIVE_STATUS = 'inactive';
    const DELETED_STATUS = 'deleted'; 

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    // protected $fillable = [
    //     'name',
    //     'email',
    //     'password',
    // ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

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
        return ['role' => 'admin'];
    } 


    public function sitePermission()
    {
        return $this->hasOne(AccesLevelDefination::class, 'user_id', 'id');
    }
    
    public function jobroster()
    {
        return $this->belongsToMany(JobNewRoster::class);
    }
    public function RolePermission()
    {
        return $this->belongsTo(RolePermission::class, 'role_id');
    }
    protected function google2faSecretAttribute()
    {
        return new Attribute([
            'get' => function ($value) {
                return decrypt($value);
            },
            'set' => function ($value) {
                return encrypt($value);
            },
        ]);
    }

}
