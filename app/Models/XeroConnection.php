<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class XeroConnection extends Model {
    protected $fillable = ['tenant_id','tenant_name','access_token','refresh_token','expires_at'];
    protected $casts    = ['expires_at' => 'datetime'];
    public function isExpired(): bool { return $this->expires_at->subMinute()->isPast(); }
}