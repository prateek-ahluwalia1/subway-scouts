import { Injectable } from '@angular/core';
import { FormBuilder, FormControl, FormGroup, Validators } from '@angular/forms';
import { NgbModal } from '@ng-bootstrap/ng-bootstrap';
import { JobshiftActivityComponent } from 'app/modules/admin/models/jobshift-activity/jobshift-activity.component';
import { StaffActivityDetailsComponent } from 'app/modules/admin/models/staff-activity-details/staff-activity-details.component';
import { JobRoster1Service } from './job-roster1.service';
import { GlobalVariable } from 'app/shared/global';
import { ProfileCommentComponent } from 'app/modules/admin/profile-comment/profile-comment.component';
import { RunsheetshiftActivityComponent } from 'app/modules/admin/models/runsheetshift-activity/runsheetshift-activity.component';

@Injectable({
  providedIn: 'root'
})
export class FormServiceOneTwoService {


  constructor(private fb: FormBuilder, private modalService: NgbModal, private rosterService: JobRoster1Service,
    private global: GlobalVariable) { }

  initializeAddShiftBasicForm(): FormGroup {
    return new FormGroup({
      site_name: new FormControl(''),
      guard_name: new FormControl(''),
      start_time: new FormControl('00:00', [Validators.required]),
      end_time: new FormControl('00:00', [Validators.required]),
      start: new FormControl(''),
      end: new FormControl(''),
      guard_id: new FormControl(''),
      site_id: new FormControl(''),
      searchValue: new FormControl(''),
    });
  }

  initializeRunsheetShiftBasicForm(): FormGroup {
    return new FormGroup({
      site_name: new FormControl(''),
      guard_name: new FormControl(''),
      start_time: new FormControl('00:00', [Validators.required]),
      end_time: new FormControl('00:00', [Validators.required]),
      start: new FormControl(''),
      end: new FormControl(''),
      guard_id: new FormControl(''),
      run_sheet_id: new FormControl(''),
      searchValue: new FormControl(''),
    });
  }


  initializeaddShiftAdvanceForm(): FormGroup {
    return new FormGroup({
      id: new FormControl(''),
      guard_name: new FormControl(''),
      guard_id: new FormControl(''),
      site_id: new FormControl(''),
      site_name: new FormControl(''),
      start_time: new FormControl('00:00'),
      end_time: new FormControl('00:00'),
      start: new FormControl('00:00'),
      end: new FormControl('00:00'),
      reason: new FormControl(''),
      unprofile_name: new FormControl(''),
      po_wo: new FormControl(''),
      shift_payable: new FormControl(''),
      shift_chargeable: new FormControl(''),
      custome_rate: new FormControl(''),
      payrate_level: new FormControl(''),
      payrate: new FormControl(''),
      // chargerate_level: new FormControl(''),
      // chargerate: new FormControl(''),
      un_published_shift: new FormControl(''),
      public_holidays: new FormControl(''),
      covid_marshal: new FormControl(''),
      training: new FormControl(''),
      // continuation: new FormControl(''),
      over_time: new FormControl(''),
      over_time_value: new FormControl(''),
      travel_time: new FormControl(''),
      travel_time_value: new FormControl(''),
      searchValue: new FormControl(''),
      custome_payrate: new FormControl(''),
      custome_chagerate: new FormControl(''),
      job_instrcutions: new FormControl(''),
      job_instruction_text: new FormControl(''),
      reimbursement: new FormControl(''),
      reimbursement_text: new FormControl(''),
      reimbursement_value: new FormControl(''),
      job_roster_tasks: this.fb.array([
      ]),
      adhoc_shift: new FormControl('')
    });
  }

  addRunsheetShiftAdvanceForm(): FormGroup {
    return new FormGroup({
      id: new FormControl(''),
      guard_name: new FormControl(''),
      guard_id: new FormControl(''),
      run_sheet_id: new FormControl(''),
      site_name: new FormControl(''),
      start_time: new FormControl('00:00'),
      end_time: new FormControl('00:00'),
      start: new FormControl('00:00'),
      end: new FormControl('00:00'),
      reason: new FormControl(''),
      unprofile_name: new FormControl(''),
      po_wo: new FormControl(''),
      shift_payable: new FormControl(''),
      shift_chargeable: new FormControl(''),
      custome_rate: new FormControl(''),
      payrate_level: new FormControl(''),
      payrate: new FormControl(''),
      chargerate_level: new FormControl(''),
      chargerate: new FormControl(''),
      un_published_shift: new FormControl(''),
      public_holidays: new FormControl(''),
      covid_marshal: new FormControl(''),
      training: new FormControl(''),
      continuation: new FormControl(''),
      over_time: new FormControl(''),
      over_time_value: new FormControl(''),
      travel_time: new FormControl(''),
      travel_time_value: new FormControl(''),
      searchValue: new FormControl(''),
      custome_payrate: new FormControl(''),
      custome_chagerate: new FormControl(''),
      job_instrcutions: new FormControl(''),
      job_instruction_text: new FormControl(''),
      run_sheet_job_roster_tasks: this.fb.array([
      ]),
    });
  }

  document_type = {
    0: "security_license",
    1: "visa"
  }

  
  RunsheetstaffDetail(type, job) {
    const typeToComponent = {
      'activity': RunsheetshiftActivityComponent,
      'detail': StaffActivityDetailsComponent,
      'note': ProfileCommentComponent
    };
    const selectedComponent = typeToComponent[type];
    let modalRef
    if (selectedComponent == typeToComponent['note']) {
      const modalConfig = { centered: true };
      modalRef = this.modalService.open(selectedComponent, modalConfig);
      modalRef.componentInstance.rosterType = 'runsheet_roster';
      this.global.guard_id = Number(job.guard_id)
      this.global.roster_id = job.roster_id
    }
    const modalConfig = { size: 'xl' };
    if (selectedComponent == typeToComponent['activity']) {
      modalRef = this.modalService.open(selectedComponent, modalConfig);
      this.global.roster_id = job.roster_id
      this.global.guard_id = Number(job.guard_id)
    }
    if (selectedComponent == typeToComponent['detail']) {
      const guard_id = Number(job.guard_id)
      let data = {
        document_type: this.document_type,
        guard_id: guard_id,
        start: this.global.start
      }
      // this.rosterService.staffData(data).subscribe(({ success, data, total_hours }) => {
      //   if (success) {
      modalRef = this.modalService.open(selectedComponent, modalConfig);
      //     modalRef.componentInstance.fromRoster = data;
      //     modalRef.componentInstance.total_hours = total_hours;
      //   }
      // })
    }

  }

}
