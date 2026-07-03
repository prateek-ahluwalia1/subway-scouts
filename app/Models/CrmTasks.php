<?php

namespace App\Models;

use App\Http\Controllers\crm\Customers;
use App\Models\crm\CustomerModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CrmTasks extends Model
{
    use HasFactory;

    public function lead()
    {
        return $this->belongsTo(CustomerModel::class, 'contact', 'id')->withDefault();
    }
}
