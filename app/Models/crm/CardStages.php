<?php

namespace App\Models\crm;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\crm\CustomerModel;
use App\Models\crm\SalePersonModel;
use App\Models\crm\CardComments;
use App\Models\User;

class CardStages extends Model
{
    use HasFactory;
    protected $table = 'stage_cards';


    public function customer()
    {
        return $this->hasOne(CustomerModel::class, 'id', 'customer_id')->select('id', 'name');
    }

    public function Stage()
    {
        return $this->hasOne(CardStages::class, 'id', 'stage_id')->select('id', 'name');
    }

    public function salesperson()
    {
        return $this->hasOne(User::class, 'id', 'saleperson_id')->select('id', 'name', 'email', 'phone')->where('userType', 'admin')->where('is_super_admin', 0);
    }

    public function admin()
    {
        return $this->hasOne(User::class, 'id', 'admin_id')->select('id', 'name');
    }

    public function comments()
    {
        return $this->hasMany(CardComments::class, 'card_id', 'id');
    }

}
