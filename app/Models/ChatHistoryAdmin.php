<?php

namespace App\Models;

use App\Http\Controllers\crm\SalesPerson;
use App\Models\crm\SalePersonModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatHistoryAdmin extends Model
{
    use HasFactory;
    protected $table = 'chat_history_admins';
    public function Sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function Receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }
    
}
