<?php

namespace App\Models\crm;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalePersonModel extends Model
{
    use HasFactory;
    protected $table = 'salepersons';

    protected $hidden = [
        'password',
    ];
}
