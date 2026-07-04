import { Component, OnInit } from '@angular/core';
import { ModalDismissReasons, NgbModal } from '@ng-bootstrap/ng-bootstrap';
import { SiteService } from 'app/services/site.service';
import { FormBuilder, FormControl, FormGroup } from '@angular/forms';
import { TrackAdminActivityService } from 'app/services/track-admin-activity.service';
import { ToastServiceService } from 'app/services/toast-service.service';
import { PatrollingService } from 'app/services/patrolling.service';
import { CreateSiteComponent } from 'app/modules/admin/models/create-site/create-site.component';
import { CreateNewAlarmComponent } from 'app/modules/admin/models/create-new-alarm/create-new-alarm.component';

@Component({
  selector: 'app-alarm-dispatch-system',
  templateUrl: './alarm-dispatch-system.component.html',
  styleUrls: ['./alarm-dispatch-system.component.scss']
})
export class AlarmDispatchSystemComponent implements OnInit {

  searchText = '';
  AddCustomrSite: FormGroup;
  constructor(private modalService: NgbModal, private siteService: SiteService, private fb: FormBuilder,
             private trackAdmin: TrackAdminActivityService, private toast: ToastServiceService,
             private patrollingService: PatrollingService) { }

  ngOnInit(): void {
    this.AddCustomrSite = this.fb.group({
      customer_id: new FormControl(''),
      site_type: new FormControl('direct'),
      site_name: new FormControl(''),
      site_description: new FormControl(''),
      site_start_date: new FormControl(''),
      site_end_date: new FormControl(''),
      job_instrcutions: new FormControl(''),
      staff_type: new FormControl('1'),
      unpublished_site: new FormControl('no'),
      site_trained: new FormControl(''),
      sos_phone: new FormControl(''),
      site_state: new FormControl(''),
      site_hours: new FormControl(''),
      po_wo: new FormControl(''),
      address: new FormControl(''),
      coordinates: new FormControl(''),
      signin_radius: new FormControl(''),
      radius_alert: new FormControl(''),
      site_level: new FormControl(''),
      payrol: new FormControl('default'),
      site_payrate_level: new FormControl(''),
      site_payrate: new FormControl(''),
      site_chargerate_level: new FormControl(''),
      site_charge_rate: new FormControl(''),
      site_break: new FormControl(''),
      site_break_payable: new FormControl(''),
      site_break_chargeable: new FormControl(''),
      break_deduction_payable: new FormControl(''),
      break_deduction_chargeable: new FormControl(''),
      welfare_call: new FormControl('no'),
      welfare_call_type: new FormControl(''),
      welfare_timing: new FormControl(''),
      green_call: new FormControl('no'),
      first_green_call: new FormControl(''),
      second_green_call: new FormControl(''),
      first_green_call_time: new FormControl(''),
      second_green_call_time: new FormControl(''),
      job_instruction_file: new FormControl(''),
      site_update_reason: new FormControl(''),
      type: new FormControl('metro'),
      site_tasks: this.fb.array([]),
      is_patrolling_site: new FormControl(false),
      monitoring_person: new FormControl(''),
      monitoring_contact: new FormControl(''),
      after_hours: new FormControl(''),
      is_alarm_patrol_site: new FormControl(''),
      internal_patrolling: new FormControl(false),
      external_patrolling: new FormControl(false),
      intermediate_patrolling: new FormControl(false),
      scanners: this.fb.array([]),
      keys: this.fb.array([]),
      alarm_panels: this.fb.array([]),
      alarm_dispatch_instruction: new FormControl(''),
      intern_no_calls: new FormControl(''),
      extern_no_calls: new FormControl(''),
      intermed_no_calls: new FormControl(''),
      intern_time_type: new FormControl(''),
      extern_time_type: new FormControl(''),
      intermed_time_type: new FormControl(''),
      intern_particular_times: this.fb.array([]),
      extern_particular_times: this.fb.array([]),
      intermed_particular_times: this.fb.array([]),
    })

    this.getAllAlarmDetails();
  }

  siteId;
  routeId;

  activity() {
    this.trackAdmin.storeActivity('Sites Page', 'Enter in Sites Page').subscribe(({ success, id }) => {
      if (success) {
        localStorage.setItem('routerId', id)
        this.routeId = id
      }
    })
  }

  createNewSite(){
    this.siteService.addSite(this.AddCustomrSite.value).subscribe(({ success, id, message }) => {
      const status = 'Location Operation!';
      if (success) {
        this.siteId = id;

        const modalRef = this.modalService.open(CreateSiteComponent, { size: 'xl', backdrop: 'static' });
        modalRef.componentInstance.patrolsiteId = this.siteId;
        modalRef.componentInstance.routeId = this.routeId;
        modalRef.result.then(
          (result) => {
            console.log(result);
    
            console.log("Modal Result", `Closed with: ${result}`);

          },
          (reason) => {
            console.log(reason);
    
            let rea = this.getDismissReason(reason)
            if (rea == 'update') {
              // this.getAllSites()
            }
          }
        );
      }
      else {
        this.toast.toastNotification(message, status);
      }

    },
    (error) => {
      this.toast.toastNotification('Something went wrong. Please contact the support team.', 'Request Incomplete!');
    }
    );
  }

  getDismissReason(reason: any): string {
    if (reason === ModalDismissReasons.ESC) {
      return "by pressing ESC";
    } else if (reason === ModalDismissReasons.BACKDROP_CLICK) {
      return "by clicking on a backdrop";
    } else {
      return `${reason}`;
    }
  }

  createNewAlarm(){
    const modalRef = this.modalService.open(CreateNewAlarmComponent, { size: 'lg' });
    // modalRef.componentInstance.fromParent = 'new';
    modalRef.result.then(
      (result) => {
        console.log(result);

        console.log("Modal Result", `Closed with: ${result}`);
      },
      (reason) => {
        console.log(reason);
        this.getAllAlarmDetails();
      }
    );
  }


  getAlarmDetails: any =[];
  getAllAlarmDetails(){
    this.patrollingService.getAllAlarmDetails().subscribe(({ success, data }) => {
      if (success) {
        this.getAlarmDetails = data;
      }
    })
  }

  editAlarmDetails(id){
    const modalRef = this.modalService.open(CreateNewAlarmComponent, { size: 'lg' });
    modalRef.componentInstance.fromParent = id;
    modalRef.result.then(
      (result) => {
        console.log(result);

        console.log("Modal Result", `Closed with: ${result}`);
      },
      (reason) => {
        console.log(reason);
        this.getAllAlarmDetails()
      }
    );
  }

  deleteAlarmDetails(id){
    this.patrollingService.delAlarmDetails(id).subscribe(({ success, status, msg }) => {
      if (success) {
        status = "Alarm Details"
        this.toast.toastNotification(msg, status)
        this.getAllAlarmDetails()
      }
    })
  }

  get filteredAlarm() {
    return this.getAlarmDetails.filter((detail) =>
    detail.name.toLowerCase().includes(this.searchText.toLowerCase())
    );
  }

}
