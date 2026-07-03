<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\crm\CardStages;

class Stage extends Model
{
    use HasFactory;

    protected $table = 'stages';


    public function cards()
    {
        return $this->hasMany(CardStages::class, 'stage_id')->select('stage_cards.*', 'stage_cards.stage_id as listId')->orderBy('created_at', 'ASC');
    }
    
}
