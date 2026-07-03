<?php

namespace App\Models;

use App\Http\Controllers\crm\SalesPerson;
use App\Models\crm\SalePersonModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatHistory extends Model
{
    use HasFactory;
    public function GuardDetails()
    {
        return $this->belongsTo(Guard::class, 'guard_id');
    }
    public function Contractor()
    {
        return $this->belongsTo(Contractor::class, 'contractor_id');
    }
    public function Admin()
    {
        return $this->belongsTo(User::class, 'admin_id', 'id');
    }
    public function SalesPerson()
    {
        return $this->belongsTo(SalePersonModel::class, 'saleperson_id', 'id');
    }
    public function Customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'id');
    }
}
