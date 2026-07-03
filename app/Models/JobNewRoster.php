<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobNewRoster extends Model
{
    use HasFactory;
    protected $table = "job_new_roster";
    
    // public function site()
    // {
    //    return $this->belongsTo(Site::class, 'site_id', 'id');
    // }


public function setUser()
{
    return json_decode($this->access_ids, true);
}


// public function users()
// {
//     return $this->belongsTo(User::class);
// }



}
