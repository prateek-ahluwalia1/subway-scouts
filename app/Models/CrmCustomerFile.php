<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CrmCustomerFile extends Model
{
    use HasFactory;
    protected $table="crm_customers_files";
    public function User()
    {
        return $this->belongsTo(User::class, 'uploaded_by')->select('name', 'id');
    }
}
