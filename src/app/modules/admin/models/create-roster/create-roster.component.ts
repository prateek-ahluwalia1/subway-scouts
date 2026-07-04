import { Component, Input, OnDestroy, OnInit, ViewChild } from '@angular/core';
import { NgbActiveModal } from '@ng-bootstrap/ng-bootstrap';
import { DateAdapter, MatOption } from '@angular/material/core';
import { AbstractControl, FormBuilder, FormControl, FormGroup, Validators } from '@angular/forms';
import { CustomerService } from 'app/services/customer.service';
import { ServiceService } from 'app/services/service.service';
import { MatDatepickerInputEvent } from '@angular/material/datepicker';
import moment from 'moment';
import { ToastServiceService } from 'app/services/toast-service.service';
import { MatSelect } from '@angular/material/select';
import { GlobalVariable } from 'app/shared/global';
import { NgxSpinnerService } from 'ngx-spinner';
import { Subject } from 'rxjs';
import { JobRoster1Service } from 'app/services/job-roster1.service';
export class Customers {
  id: number;
  name: string;
}
export class Sites {
  id: number;
  site_name: string;
}
interface Admin {
  id: string;
  name: string;
}

@Component({
  selector: 'app-create-roster',
  templateUrl: './create-roster.component.html',
  styleUrls: ['./create-roster.component.scss']
})
export class CreateRosterComponent implements OnInit, OnDestroy {

  @Input() rosterId;
  createNewRoster: FormGroup
  type = 'create-roster'
  formBuilder: any;
  submitted = false;
  starDate: string;
  endDate: string;

  private ngUnsubscribe = new Subject();
  allRosters: any = []
  seletedAdmins: any = []
  constructor(public ngbActiveModal: NgbActiveModal,
    public dateAdapter: DateAdapter<Date>,
    private cus: CustomerService,
    private service: ServiceService,
    private fb: FormBuilder,
    public toast: ToastServiceService,
    public global: GlobalVariable,
    private spinner: NgxSpinnerService,
    private rosterService: JobRoster1Service
  ) {
    this.dateAdapter.setLocale('en-AU');

  }

  customers: Customers[] = [];
  sites: Sites[] = [];
  adminList: Admin[] = [];
  rosterData
  ngOnInit(): void {

    this.createNewRoster = this.fb.group({
      roster_name: ['', Validators.required],
      start: ['', Validators.required],
      end: [''],
      customer_id: ['', Validators.required],
      site_id: ['', Validators.required],
      // state: ['Victoria', Validators.required],
      adminId: ['', Validators.required]
    });
    this.service.getAllAdmins().subscribe(({ success, data }) => {
      if (success) {
        this.adminList = data;
      }
    });

    this.cus.getCust().subscribe(({ success, data }) => {
      if (success) {
        this.customers = data;
      }
    });

    this.rosterService.allRoster$.subscribe(rosters => {
      this.allRosters = rosters;
    });
    if (this.rosterId) {
      this.setRosterValues()
    }

  }


  get f(): { [key: string]: AbstractControl } {
    return this.createNewRoster.controls;
  }

  receiveDataFromChildSite(data: any) {
    const ids = data?.map(item => item.id);
    this.createNewRoster.get('site_id').setValue(ids)
  }

  fromMultiCustomer
  receiveDataFromChild(data: string) {
    this.fromMultiCustomer = data;
    const ids = this.fromMultiCustomer.value.map(item => item.id);
    this.createNewRoster.get('customer_id').setValue(ids)
    if (this.fromMultiCustomer) {
      this.getSites()
    }
  }

  close(data?) {
    this.global.selectedCustomers = []
    this.ngbActiveModal.dismiss(data);
  }


  addNewRoster(value) {
    this.submitted = true
    this.spinner.show()
    if (this.createNewRoster.invalid) {
      this.spinner.hide()
      return;
    }
    const rosterName = value.roster_name;
    // for (const element of this.allRosters?.data || [] && !this.createNewRoster.value.id) {
    //   if (element.roster_name === rosterName) {
    //     this.toast.toastNotification1('This roster name has already been taken', 'Duplicate Name');
    //     this.spinner.hide();
    //     return;
    //   }
    // }

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
    this.createNewRoster.value.user_id = this.seletedAdmins

    let status = 'Roster Operation'
    if (this.rosterId) {
      value.id = this.rosterId.id
      this.service.updateNewRoster(value).subscribe(({ success, message }) => {
        if (success) {
          this.toast.toastNotification(message, status)
          this.close(success)
        }
        else {
          this.toast.toastNotification(message, status)
        }
        this.spinner.hide()
      }, (error => {
        this.toast.toastNotification('Something went wrong. Please contact with ', 'Request Incomplete!')
      }))
    }
    else {
      this.service.saveNewRoster(value).subscribe(({ success, message }) => {
        if (success) {
          this.toast.toastNotification(message, status)
          this.close(success)
        }
        else {
          this.toast.toastNotification(message, status)
        }
        this.spinner.hide()
      }, (error => {
        this.toast.toastNotification('Something went wrong. Please contact with ', 'Request Incomplete!')
        this.spinner.hide()
      }))
    }
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

  getSites() {
    let data = {
      customer_id: this.createNewRoster.value.customer_id,
      // state: this.createNewRoster.value.state
    }
    this.service.getMutilCusMutilSite(data).subscribe(({ success, data }) => {
      if (success) {
        this.sites = data
      }
    },
      (error => {
        console.log(error);
      })
    )
  }
  onStateSelectionChange(event: any) {
    if (this.fromMultiCustomer) {
      this.getSites()
    }
  }

  ngOnDestroy() {
    this.ngUnsubscribe.next(this.ngUnsubscribe);
    this.ngUnsubscribe.complete();
    this.global.selectedSite = []
    this.global.selectedCustomers = []
    this.global.selectedGuards = []
  }

  receiveDataFromChildAdmins(data: any) {
    this.seletedAdmins = data?.value.map(item => item.id)
    this.createNewRoster.get('adminId').setValue(this.seletedAdmins)
  }

  setRosterValues() {
    let start = moment(this.rosterId.start, 'YYYY-MM-DD');
    this.global.selectedSite = this.rosterId.site_id
    this.global.selectedCustomers = this.rosterId.customer_id
    this.global.selectedGuards = this.rosterId.user_id
    if (this.rosterId.end) {
      let end = moment(this.rosterId.end, 'YYYY-MM-DD')
      this.createNewRoster.get('end').setValue(end.format())
    }
    this.createNewRoster.get('roster_name').setValue(this.rosterId.roster_name)
    this.createNewRoster.get('start').setValue(start.format())
    // this.createNewRoster.get('state').setValue(this.rosterId.state)
  }
}
