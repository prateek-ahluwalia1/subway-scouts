<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class BeforeUpdatePayratesResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
                'Title' => $this->title,
                'Customer Id' => $this->customer_id * 1,
                'Position' => $this->position,
                'Level' => $this->level,
                'State' => $this->state,
                'Default Metro Mon To Fri Day Rate' => $this->def_metro_mon_to_fri_day_rate,
                'Default Metro Mon To Fri Night Rate' => $this->def_metro_mon_to_fri_night_rate,
                'Default Metro Sat Day Rate' => $this->def_metro_sat_day_rate,
                'Default Metro Sat Night Rate' => $this->def_metro_sat_night_rate,
                'Default Metro Sun Day Rate' => $this->def_metro_sun_day_rate,
                'Default Metro Sun Night Rate' => $this->def_metro_sun_night_rate,
                'Default Metro Pub Holi Day Rate' => $this->def_metro_pub_holi_day_rate,
                'Default Metro Pub Holi Night Rate' => $this->def_metro_pub_holi_night_rate,
                'Default Reg Mon To Fri Day Rate' => $this->def_reg_mon_to_fri_day_rate,
                'Default Reg Mon To Fri Night Rate' => $this->def_reg_mon_to_fri_night_rate,
                'Default Reg Sat Day Rate' => $this->def_reg_sat_day_rate,
                'Default Reg Sat Night Rate' => $this->def_reg_sat_night_rate,
                'Default Reg Sun Day Rate' => $this->def_reg_sun_day_rate,
                'Default Reg Sun Night Rate' => $this->def_reg_sun_night_rate,
                'Default Reg Pub Holi Day Rate' => $this->def_reg_pub_holi_day_rate,
                'Default Reg Pub Holi Night Rate' => $this->def_reg_pub_holi_night_rate,
                'EBA Metro Mon To Fri Day Rate' => $this->eba_metro_mon_to_fri_day_rate,
                'EBA Metro Mon To Fri Night Rate' => $this->eba_metro_mon_to_fri_night_rate,
                'EBA Metro Sat Day Rate' => $this->eba_metro_sat_day_rate,
                'EBA Metro Sat Night Rate' => $this->eba_metro_sat_night_rate,
                'EBA Metro Sun Day Rate' => $this->eba_metro_sun_day_rate,
                'EBA Metro Sun Night Rate' => $this->eba_metro_sun_night_rate,
                'EBA Metro Pub Holi Day Rate' => $this->eba_metro_pub_holi_day_rate,
                'EBA Metro Pub Holi Night Rate' => $this->eba_metro_pub_holi_night_rate,
                'EBA Reg Mon To Fri Day Rate' => $this->eba_reg_mon_to_fri_day_rate,
                'EBA Reg Mon To Fri Night Rate' => $this->eba_reg_mon_to_fri_night_rate,
                'EBA Reg Sat Day Rate' => $this->eba_reg_sat_day_rate,
                'EBA Reg Sat Night Rate' => $this->eba_reg_sat_night_rate,
                'EBA Reg Sun Day Rate' => $this->eba_reg_sun_day_rate,
                'EBA Reg Sun Night Rate' => $this->eba_reg_sun_night_rate,
                'Award Metro Mon To Fri Day Rate' => $this->award_metro_mon_to_fri_day_rate,
                'Award Metro Mon To Fri Night Rate' => $this->award_metro_mon_to_fri_night_rate,
                'Award Metro Sat Day Rate' => $this->award_metro_sat_day_rate,
                'Award Metro Sat Night Rate' => $this->award_metro_sat_night_rate,
                'Award Metro Sun Day Rate' => $this->award_metro_sun_day_rate,
                'Award Metro Sun Night Rate' => $this->award_metro_sun_night_rate,
                'Award Metro Pub Holi Day Rate' => $this->award_metro_pub_holi_day_rate,
                'Award Metro Pub Holi Night Rate' => $this->award_metro_pub_holi_night_rate,
                'Award Reg Mon To Fri Day Rate' => $this->award_reg_mon_to_fri_day_rate,
                'Award Reg Mon To Fri Night Rate' => $this->award_reg_mon_to_fri_night_rate,
                'Award Reg Sat Day Rate' => $this->award_reg_sat_day_rate,
                'Award Reg Sat Night Rate' => $this->award_reg_sat_night_rate,
                'Award Reg Sun Day Rate' => $this->award_reg_sun_day_rate,
                'Award Reg Sun Night Rate' => $this->award_reg_sun_night_rate,
                'Award Reg Pub Holi Day Rate' => $this->award_reg_pub_holi_day_rate,
                'Award Reg Pub Holi Night Rate' => $this->award_reg_pub_holi_night_rate,
                'OT Base Rate' => $this->ot_base_rate,
        ];
    }
}
