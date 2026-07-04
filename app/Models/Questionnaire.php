<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Questionnaire extends Model
{
    use HasFactory;

    protected $table = 'questionnaires';

    // public function QuestionDetails(){
    //     return $this->belongsTo(QuestionDetails::class, 'questionnaires_id');
    // }
}
