<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccesLevelDefination extends Model
{
    use HasFactory;

    protected $table = "acces_level_defination";

    // public function permisstions()
    // {
    //     return $this->hasMany(AccesLevelDefination::class, 'id', 'access_level_id');
    // }
}
