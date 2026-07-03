<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CrmNotification extends Model
{
    use HasFactory;

    protected $table="crm_notifications";
    public function SendBy()
    {
        return $this->belongsTo(User::class, 'send_by', 'id')->select('id', 'name')->withDefault();
    }
    public function SendTo()
    {
        return $this->belongsTo(User::class, 'send_to', 'id')->select('id', 'name')->withDefault();
    }

}
