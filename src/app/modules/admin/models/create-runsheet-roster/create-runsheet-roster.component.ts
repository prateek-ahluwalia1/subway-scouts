import { Component, Input, OnInit } from '@angular/core';
import { AbstractControl, FormBuilder, FormGroup, Validators } from '@angular/forms';
import { DateAdapter } from '@angular/material/core';
import { MatDatepickerInputEvent } from '@angular/material/datepicker';
import { NgbActiveModal } from '@ng-bootstrap/ng-bootstrap';
import { CustomerService } from 'app/services/customer.service';
import { RunsheetrosterService } from 'app/services/runsheetroster.service';
import { ServiceService } from 'app/services/service.service';
import { ToastServiceService } from 'app/services/toast-service.service';
import { GlobalVariable } from 'app/shared/global';
import moment from 'moment';
import { NgxSpinnerService } from 'ngx-spinner';
import { Subject } from 'rxjs';
export class Customers {
  id: number;
  name: string;
}

interface Admin {
  id: string;
  name: string;
}

@Component({
  selector: 'app-create-runsheet-roster',
  templateUrl: './create-runsheet-roster.component.html',
  styleUrls: ['./create-runsheet-roster.component.scss']
})
export class CreateRunsheetRosterComponent implements OnInit {

  type = 'create-roster'
  starDate: string;
  endDate: string;
  customers: Customers[] = [];
  adminList: Admin[] = [];
  createRunsheetRoster: FormGroup
  @Input() rosterId;
  submitted = false;
  // formBuilder: any;

  private ngUnsubscribe = new Subject();
  allRosters: any = []
  seletedAdmins: any = []
  // rosterData

  constructor(public ngbActiveModal: NgbActiveModal, private cus: CustomerService, private service: ServiceService,
    private fb: FormBuilder, public dateAdapter: DateAdapter<Date>, private roster: RunsheetrosterService,
    public toast: ToastServiceService, public global: GlobalVariable, private spinner: NgxSpinnerService,) { 

      this.dateAdapter.setLocale('en-AU');
    }

  ngOnInit(): void {

    this.createRunsheetRoster = this.fb.group({
      name: ['', Validators.required],
      start: ['', Validators.required],
      end: [''],
      customers: [''],
      admins: [''],
    });

    this.cus.getCust().subscribe(({ success, data }) => {
      if (success) {
        this.customers = data;
      }
    });

    this.service.getAllAdmins().subscribe(({ success, data }) => {
      if (success) {
        this.adminList = data;
      }
    });

    this.roster.allRoster$.subscribe(rosters => {
      this.allRosters = rosters.data;
      console.log("create roster", this.allRosters)
    });

    if (this.rosterId) {
      this.setRosterValues()
    }
  }

  close(data) {
    this.ngbActiveModal.dismiss(data);
  }

  get f(): { [key: string]: AbstractControl } {
    return this.createRunsheetRoster.controls;
  }

  fromMultiCustomer
  receiveDataFromChild(data: string) {
    this.fromMultiCustomer = data;
    const ids = this.fromMultiCustomer.value.map(item => item.id);
    this.createRunsheetRoster.get('customers').setValue(ids)
  }

  // date picker
  addEvent(type: string, event: MatDatepickerInputEvent<Date>) {
      let calendertype
      if (calendertype == 'start') {
        this.starDate = moment(event.value).format('DD/MM/YYYY');
      }
      else {
        this.endDate = moment(event.value).format('DD/MM/YYYY');
      }
  }

  receiveDataFromChildAdmins(data: any) {
    this.seletedAdmins = data?.value.map(item => item.id)
    this.createRunsheetRoster.get('admins').setValue(this.seletedAdmins)
  }

  Submit(value){
    this.submitted = true
    this.spinner.show()
    if (this.createRunsheetRoster.invalid) {
      this.spinner.hide()
      return;
    }

    value.start = moment(value.start).format('MM-DD-YYYY')
    if (value.end) {
      value.end = moment(value.end).format('MM-DD-YYYY')
    }
    else {
      value.end = null
    }
    value.admin_id = this.global.admin.admin_id
    const currentDate = moment()
    if (moment(value.end) < moment(value.start)) {
      let msg = "End date cannot be less than start date"
      let status = 'Roster Operation!'
      this.toast.toastNotification1(msg, status)
      this.spinner.hide()
      return
    }
    else if (!this.rosterId && moment(value.start).isBefore(currentDate, 'day')) {
      let msg = "Start date can't be less than current date"
      let status = 'Roster Operation!'
      this.toast.toastNotification1(msg, status)
      this.spinner.hide()
      return
    }
    this.createRunsheetRoster.value.admins = this.seletedAdmins
    if(this.rosterId){
      let status = 'Roster Operation'
      value.id = this.rosterId.id
      this.roster.updateRoster(value).subscribe(({success, message }) => {
        if (success) {
          this.toast.toastNotification(message, status)
          this.close(success);
          this.spinner.hide()
        }
        else {
          this.toast.toastNotification(message, status)
        }
        this.spinner.hide()
      }, (error => {
        this.toast.toastNotification('Something went wrong. Please contact with ', 'Request Incomplete!')
      }))
    }
    else{
      let status = 'Roster Operation'
      this.roster.createRoster(value).subscribe(({ success, message }) => {
        if (success) {
          this.toast.toastNotification(message, status)
          this.close(success);
          this.spinner.hide()
        }
        else {
          this.toast.toastNotification(message, status)
        }
      }, (error => {
        this.toast.toastNotification('Something went wrong. Please contact with ', 'Request Incomplete!')
        this.spinner.hide()
      }))
    }
  }

  ngOnDestroy() {
    this.ngUnsubscribe.next(this.ngUnsubscribe);
    this.global.selectedGuards = []
  }

  setRosterValues() {
    let start = moment(this.rosterId.start, 'DD-MM-YYYY');
    this.global.selectedGuards = this.rosterId.admins
    if (this.rosterId.end) {
      let end = moment(this.rosterId.end, 'DD-MM-YYYY')
      this.createRunsheetRoster.get('end').setValue(end.format())
    }
    this.createRunsheetRoster.get('customers').setValue(this.rosterId.customers.id)
    this.createRunsheetRoster.get('name').setValue(this.rosterId.name)
    this.createRunsheetRoster.get('start').setValue(start.format())
  }

}
