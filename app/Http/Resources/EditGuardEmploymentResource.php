<?php

namespace App\Http\Resources;


use App\Models\GuardDocument;
use App\Models\GuardInduction;
use App\Models\Payrate;
use Illuminate\Http\Resources\Json\JsonResource;

class EditGuardEmploymentResource extends JsonResource
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
            'id' => $this->id,
            'guard_id' => $this->guard_id,
            'other_name' => $this->other_name,
            'hired_on' => usaToAus($this->hired_on),
            'induction'=> GuardInductionResource::collection($this->inductions),
            'payrate_state' => $this->payrate_state,
            'payrate' => $this->payrate,
            'payrate_name' => Payrate::where('id', $this->payrate)->select('name')->first()->name ?? 'N/A',
            'job_level' => $this->job_level,
            'tfn_file' => returnImgPath('guard_employment_details',$this->tfn_file),
            'tfn_file_no' => $this->tfn_file_no,
            'superannutation_file' => returnImgPath('guard_employment_details',$this->superannutation_file),
            'superannuation_no' => $this->superannutation_no,
            'superannuation_fund' => $this->superannuation_fund,
            'superannutation_fund_usi' => $this->superannuation_fund_usi,
            'member_number' => $this->member_number,
            'account_holder' => $this->account_holder,
            'superannutation_name' => $this->superannutation_name,
            'abn_name' => $this->abn_name,
            'abn_no' => $this->abn_no,
            'bank_name' => $this->bank_name,
            'bsb' => $this->bsb,
            'bank_account_no' => $this->bank_account_no,
            'letter_url' => $this->letter_url,
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
            'limit_exceed' => $this->limit_exceed,
            'work_hours_limitation_status' => ($this->work_hours_limitation_status == 1 ? true : false),
            'weekly_work_hours_limitation' => $this->weekly_work_hours_limitation,
            'authorized_by' => $this->authorized_by,
            'customer_id' => $this->customer_id,
            'guard_document_type' => $this->guard_document_type,
            'letter_from_educational_institute' =>  $this->letter_from_educational_institute,
            'internal_id' => !empty($this->guardz) && !empty($this->guardz->internal_id) ? $this->guardz->internal_id : 'N/A',
            'created_at' => usaToAus($this->created_at),

        ];
    }
}
