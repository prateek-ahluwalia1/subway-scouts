<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class LoginActiviteResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        
        // elseif($this->action_on  == 'guards_induction'){
        //     $beforeUpdateResource = !empty($this->data) ? new BeforeUpdateGuardsInductionResource(json_decode($this->data)) : '';
        // }
        // elseif($this->action_on  == 'staff_uniforms'){
        //     $beforeUpdateResource = !empty($this->data) ? new BeforeUpdateStaffUniformResource(json_decode($this->data)) : '';  //remain
        // }
        // $old = !empty($this->data) && ($this->data !== null) && ($this->data !== 'null') && ($this->data !== '') ? $this->data : '';
        // $dd = json_decode($old, true);
        // // return $dd; 

        $beforeUpdateResource = '';
        if($this->action_on  == 'chargerates'){
            $beforeUpdateResource = !empty($this->data) ? new BeforeUpdateChargerateResource(json_decode($this->data)) : '';
        }elseif($this->action_on  == 'sms_history'){
            $beforeUpdateResource = !empty($this->data) ? new BeforeUpdateSmsHistoryResource(json_decode($this->data)) : '';
        }elseif($this->action_on  == 'contractors'){  // ok
            $beforeUpdateResource = !empty($this->data) ? new BeforeUpdateContractorResource(json_decode($this->data)) : '';
        }elseif($this->action_on  == 'contractor_documents'){ //ok
            $beforeUpdateResource = !empty($this->data) ? new ContractorOtherDocumentsResource(json_decode($this->data)) : '';
        }elseif($this->action_on  == 'contractor_more_contacts'){ //ok
            $beforeUpdateResource = !empty($this->data) ? new ContractorMoreContactsResource(json_decode($this->data)) : '';
        }elseif($this->action_on  == 'customers'){  //ok 
            $beforeUpdateResource = !empty($this->data) ? new BeforeUpdateCustomerResource(json_decode($this->data)) : '';
        }elseif($this->action_on  == 'customer_documents'){ //ok
            $beforeUpdateResource = !empty($this->data) ? new BeforeUpdateCustomerDocumentsResource(json_decode($this->data)) : '';
        }elseif($this->action_on  == 'customer_more_contacts'){  //ok
            $beforeUpdateResource = !empty($this->data) ? new BeforeUpdateCustomerMoreContactResource(json_decode($this->data)) : '';
        }elseif($this->action_on  == 'emails'){  //remain
            $beforeUpdateResource = !empty($this->data) ? new BeforeUpdateEmailsResource(json_decode($this->data)) : '';
        }elseif($this->action_on  == 'email_signatures'){  //ok 
            $beforeUpdateResource = !empty($this->data) ? new BeforeUpdateEmailSignaturesResource(json_decode($this->data)) : '';
        }elseif($this->action_on  == 'email_templates'){ // remail testing
            $beforeUpdateResource = !empty($this->data) ? new BeforeUpdateEmailTemplateResource(json_decode($this->data)) : '';
        }elseif($this->action_on  == 'form_templates'){  // remail testing
            $beforeUpdateResource = !empty($this->data) ? new BeforeUpdateFormTemplateResource(json_decode($this->data)) : '';
        }elseif($this->action_on  == 'Staff'){  //ok
            $beforeUpdateResource = !empty($this->data) ? new BeforeUpdateNewGuardResource(json_decode($this->data)) : '';
        }elseif($this->action_on  == 'staff_documents'){  //ok
            $beforeUpdateResource = !empty($this->data) ? new BeforeUpdateStaffDocumentResource(json_decode($this->data)) : '';
        }elseif($this->action_on  == 'staff_work_details'){  //remain testing
            $beforeUpdateResource = !empty($this->data) ? new BeforeUpdateStaffWorkDetailResource(json_decode($this->data)) : '';
        }elseif($this->action_on  == 'staff_feedbacks'){  //remain testing
            $beforeUpdateResource = !empty($this->data) ? new BeforeUpdateStaffFeedbackResource(json_decode($this->data)) : '';
        }elseif($this->action_on  == 'staff_internal_and_external_ids'){  //ok
            $beforeUpdateResource = !empty($this->data) ? new BeforeUpdateStaffInternalAndExternalIdResource(json_decode($this->data)) : '';
        }elseif($this->action_on  == 'new_job_roster'){  //ok
            $beforeUpdateResource = !empty($this->data) ? new BeforeUpdateNewJobRosterResource(json_decode($this->data)) : '';
        }elseif($this->action_on  == 'job_roster'){  //ok
            $beforeUpdateResource = !empty($this->data) ? new BeforeUpdateResource(json_decode($this->data)) : '';
        }elseif($this->action_on  == 'job_roster_tasks'){  //ok
            $beforeUpdateResource = !empty($this->data) ? new BeforeUpdateJobRosterTasksResource(json_decode($this->data)) : '';
        }elseif($this->action_on  == 'payrates'){  //remian tesing
            $beforeUpdateResource = !empty($this->data) ? new BeforeUpdatePayratesResource(json_decode($this->data)) : '';
        }elseif($this->action_on  == 'sms_templates'){  //remian tesing
            $beforeUpdateResource = !empty($this->data) ? new BeforeUpdateSmsTemplatesResource(json_decode($this->data)) : '';
        }elseif($this->action_on  == 'crm_customers'){  //remian tesing
            $beforeUpdateResource = !empty($this->data) ? new BeforeUpdateCrmCustomerResource(json_decode($this->data)) : '';
        }elseif($this->action_on  == 'scrumboards'){  //remian tesing
            $beforeUpdateResource = !empty($this->data) ? new BeforeUpdateScrumboardResource(json_decode($this->data)) : '';
        }elseif($this->action_on  == 'stages'){  //remian tesing
            $beforeUpdateResource = !empty($this->data) ? new BeforeUpdateStagesResource(json_decode($this->data)) : '';
        }elseif($this->action_on  == 'stage_cards'){  //remian tesing
            $beforeUpdateResource = !empty($this->data) ? new BeforeUpdateStageCardResource(json_decode($this->data)) : '';
        }elseif($this->action_on  == 'card_comments'){  //remian tesing
            $beforeUpdateResource = !empty($this->data) ? new BeforeUpdateCardCommentResource(json_decode($this->data)) : '';
        }elseif($this->action_on  == 'customer_comments'){  //remian tesing
            $beforeUpdateResource = !empty($this->data) ? new BeforeUpdateCustomerCommentsResource(json_decode($this->data)) : '';
        }elseif($this->action_on  == 'users'){  //ok
            $beforeUpdateResource = !empty($this->data) ? new BeforeUpdateAdminResource(json_decode($this->data)) : '';
        }elseif($this->action_on  == 'location'){  //ok
            $beforeUpdateResource = !empty($this->data) ? new AllSitesResource(json_decode($this->data)) : '';
        }

        return [
            'id' => $this->id,
            'action_by' => getAdminName($this->action_by),
            'action_type' => returnAction($this->action_type),
            'action_on' => $this->action_on,
            'roster_id' => $this->roster_id,
            'date' => dateFormat($this->created_at),
            'time' => timeFormat($this->created_at),
            'before_update' => $beforeUpdateResource,
        ];
    }
}
