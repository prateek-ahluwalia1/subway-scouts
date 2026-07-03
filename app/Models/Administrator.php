<?php

namespace App\Models;

use GuzzleHttp\Psr7\Request;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Administrator extends Model
{
    use HasFactory;
    protected $table="administrators";
    const ACTIVE_STATUS = 'active';
    const INACTIVE_STATUS = 'inactive';
    const DELETED_STATUS = 'deleted';

   
    
}
