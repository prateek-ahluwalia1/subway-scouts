<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatHistoryContractor extends Model
{
    use HasFactory;

    protected $table = 'chat_history_contractors';

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
    public function contractor()
    {
        return $this->belongsTo(Contractor::class, 'contractor_id', 'id');
    }
}
