<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GuardDocument extends Model
{
    protected $table="guards_documents";
    use HasFactory;

    protected $guarded = [];

    public function GuardDetails()
    {
        return $this->belongsTo(Guard::class, 'guard_id')->select('id', 'first_name', 'last_name', 'guard_status');
    }
}
