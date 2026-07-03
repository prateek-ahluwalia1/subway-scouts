<?php

namespace App\Models\crm;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class CardComments extends Model
{
    use HasFactory;
    protected $table = 'card_comments';
    
    public function admin()
    {
        return $this->hasOne(User::class, 'id', 'admin_id')->select('id', 'name');
    }

    public function card()
    {
        return $this->belongsTo(CardStages::class, 'id', 'card_id');
    }


}
