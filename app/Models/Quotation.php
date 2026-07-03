<?php

namespace App\Models;

use App\Models\crm\CustomerModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Quotation extends Model
{
    use HasFactory;

    protected $table = 'quotations';

    public function Lead(){
        return $this->belongsTo(CustomerModel::class, 'contacted_id')->select('id','name','street', 'state', 'country', 'city', 'zip_code');
    }
}
