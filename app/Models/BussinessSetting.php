<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BussinessSetting extends Model
{
    use HasFactory;
    protected $connection = 'mysql2';
    protected $table = 'business_data';


    public function businessConfig()
    {
        return $this->hasMany(BusinessConfig::class, 'business_data_id', 'id');
    }
    
}


