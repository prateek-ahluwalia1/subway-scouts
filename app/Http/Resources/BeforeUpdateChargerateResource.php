<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class BeforeUpdateChargerateResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return[
            'id' => $this->id,
            'Admin Name' => !empty($this->admin_id) ? getAdminName($this->admin_id) : 'N/A',
            'Award Metro Mon To Fri Day Rate' => $this->award_metro_mon_to_fri_day_rate,
            'Award Metro Mon To Fri Night Rate' => $this->award_metro_mon_to_fri_night_rate,
            'Award Metro Pub Holi Day Rate' => $this->award_metro_pub_holi_day_rate,
            'Award Metro Sat Day Rate' => $this->award_metro_sat_day_rate,
            'Award Metro Sun Day Rate' => $this->award_metro_sun_day_rate,
            'Award Reg Mon To Fri Day Rate' => $this->award_reg_mon_to_fri_day_rate,
            'Award Reg Mon To Fri Night Rate' => $this->award_reg_mon_to_fri_night_rate,
            'Award Reg Pub Holi Day Rate' => $this->award_reg_pub_holi_day_rate,
            'Award Reg Sat Day Rate' =>  $this->award_metro_sun_day_rate,
            'Award Reg Sun Day Rate' => $this->award_reg_sun_day_rate,
            'Customer Name'  => !empty($this->customer_id) ? getCustomerName($this->customer_id) : 'N/A',
            'Def Metro Mon To Fri Day Rate' => $this->def_metro_mon_to_fri_day_rate,
            'Def Metro Mon To Fri Night Rate' => $this->def_metro_mon_to_fri_night_rate,
            'Def Metro Pub Holi Day Rate' => $this->def_metro_pub_holi_day_rate,
            'Def Metro Sat Day Rate' => $this->def_metro_sat_day_rate,
            'Def Metro Sun Day Rate' => $this->def_metro_sun_day_rate,
            'Def Reg Mon To Fri Day Rate' => $this->def_reg_mon_to_fri_day_rate,
            'Def Reg Mon To Fri Night Rate' => $this->def_reg_mon_to_fri_night_rate,
            'Def Reg Pub Holi Day Rate' => $this->def_reg_pub_holi_day_rate,
            'Def Reg Sat Day Rate' => $this->def_reg_sat_day_rate,
            'Def Reg Sun Day Rate' => $this->def_reg_sun_day_rate,
            'EBA Metro Mon To Fri Day Rate' => $this->eba_metro_mon_to_fri_day_rate,
            'EBA Metro Mon To Fri Night Rate' => $this->eba_metro_mon_to_fri_night_rate,
            'EBA Metro Pub Holi Day Rate' => $this->eba_metro_pub_holi_day_rate,
            'EBA Metro Sat Day Rate' => $this->eba_metro_sat_day_rate,
            'EBA Metro Sun Day Rate' => $this->eba_metro_sun_day_rate,
            'EBA Reg Mon To Fri Day Rate' => $this->eba_reg_mon_to_fri_day_rate,
            'EBA Reg Mon To Fri Night Rate' => $this->eba_reg_mon_to_fri_night_rate,
            'EBA Reg Pub Holi Day Rate' => $this->eba_reg_pub_holi_day_rate,
            'EBA Reg Sat Day Rate' => $this->eba_reg_sat_day_rate,
            'EBA Reg Sun Day Rate' => $this->eba_reg_sun_day_rate,
            'Level' => $this->level,
            'OT Base Rate' => $this->ot_base_rate,
            'Position' => $this->position,
            'State' => $this->state,
            'Title' => $this->title,
        ];
    }
}
