<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class XeroEmployeeMap extends Model {
    protected $table    = 'xero_employee_map';
    protected $fillable = ['crm_employee_id','xero_employee_id','last_synced_at', 'xero_first_name', 'xero_last_name', 'xero_payslip_id', 'ordinary_earnings_rate_id'];
    protected $casts    = ['last_synced_at' => 'datetime'];
    public function guardz() { return $this->belongsTo(Guard::class, 'crm_employee_id'); }
}