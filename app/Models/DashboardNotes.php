<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DashboardNotes extends Model
{
    use HasFactory;

    protected $table = 'dashboard_notes';

    protected $guarded = [];
}
