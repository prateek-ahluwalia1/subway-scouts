<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChargeRate extends Model
{
    use HasFactory;

    protected $table = 'charged_rates';

    protected $fillable = [
        'title',
        'state',
        'level',
        'customer_id',
        'position',
        'flat_metro_week_day',
        'flat_metro_weekend',
        'flat_metro_public_holiday',
        'flat_regional_week_day',
        'flat_regional_weekend',
        'flat_regional_public_holiday',
        'eba_metro_weekday_day',
        'eba_metro_weekday_afternoon',
        'eba_metro_weekday_night',
        'eba_metro_weekend',
        'eba_metro_public_holiday',
        'eba_regional_weekday_day',
        'eba_regional_weekday_afternoon',
        'eba_regional_weekday_night',
        'eba_regional_weekend',
        'eba_regional_weekend_sun',
        'eba_metro_weekend_sun',
        'eba_regional_public_holiday',
        'flat_metro_week_day_day',
        'flat_regional_week_day_day',
        'flat_metro_week_day_night',
        'flat_regional_week_day_night',
        'flat_metro_friday',
        'flat_regional_friday',
        'flat_metro_saturday',
        'flat_regional_saturday',
        'flat_metro_sunday',
        'flat_regional_sunday',
        'flat_regional_sunday_night',
        'flat_metro_sunday_night',
        'flat_metro_saturday_night',
        'flat_regional_saturday_night',
        'flat_metro_public_holiday_night',
        'flat_regional_public_holiday_night',
        'eba_metro_saturday_day',
        'eba_regional_saturday_day',
        'eba_metro_saturday_night',
        'eba_regional_saturday_night',
        'eba_metro_sunday_day',
        'eba_regional_sunday_day',
        'eba_metro_sunday_night',
        'eba_regional_sunday_night',
        'eba_metro_public_holiday_night',
        'eba_regional_public_holiday_night',
        'normal_rate',
        'eba_rate'
    ];

        // Cast fields to specific data types
        protected $casts = [
            'normal_rate' => 'array',
            'eba_rate' => 'array',
        ];
}
