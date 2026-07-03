<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Stage;

class Scrumboard extends Model
{
    use HasFactory;
    protected $table = 'scrumboards';

    public function lists()
    {
        return $this->hasMany(Stage::class, 'scrumboard_id', 'id')->orderBy('created_at', 'ASC');
    }
}
