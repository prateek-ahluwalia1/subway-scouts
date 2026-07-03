<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FormTemplate extends Model
{
    use HasFactory;

    public function submittedCount()
    {
        return $this->hasMany(DynamicFormHistory::class, 'form_id', 'id')->whereNotNull('form_data');
    }
}
