<?php

namespace App\Models\crm;

use App\Models\User;
use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerModel extends Model
{
    use HasFactory;
    protected $table = 'crm_customers';

    protected $hidden = [
        'password',
    ];

    protected $fillable = [
        'name',
        'email',
        'phone',
        'company',
        'saleperson_id',
        'fax',
        'website',
        'title',
        'lead_source',
        'industry',
        'no_emp',
        'annual_revenue',
        'rating',
        'skype_id',
        'secondary_email',
        'twitter',
        'street',
        'state',
        'country',
        'city',
        'zip_code',
        'description',
        'password'
    ];


    public function CreatedBy()
    {
        return $this->hasOne(User::class, 'id', 'created_by');
    }
    public function HandledBy()
    {
        return $this->hasOne(User::class, 'id', 'saleperson_id');
    }
    public function AssignOperation()
    {
        return $this->hasOne(User::class, 'id', 'assign_operation');
    }
    public function LeadClientName()
    {
        return $this->hasOne(Customer::class, 'id', 'leaad_client_name');
    }
}
