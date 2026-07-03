<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatHistoryGuard extends Model
{
    use HasFactory;
    protected $table = 'chat_history_guards';
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
    public function guardz()
    {
        return $this->belongsTo(Guard::class, 'staff_id', 'id');
    }
}
