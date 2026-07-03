<?php

namespace App\Models;

use App\Models\crm\CustomerModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class EmailHistory extends Model
{
    use HasFactory;

    protected $fillable = ['lead_id', 'to', 'subject', 'message', 'attachments'];
    
    public function User(){
        return $this->belongsTo(User::class)->select('id','name');
    }
    public function Lead(){
        return $this->belongsTo(CustomerModel::class)->select('id','name');
    }
}
