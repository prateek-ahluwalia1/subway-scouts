import { ChangeDetectionStrategy, ChangeDetectorRef, Component, OnDestroy, OnInit } from '@angular/core';
import { FormControl, FormGroup } from '@angular/forms';
import { DateAdapter } from '@angular/material/core';
import { ActivatedRoute } from '@angular/router';
import { NgbModal } from '@ng-bootstrap/ng-bootstrap';
import { CustomerService } from 'app/services/customer.service';
import { ReportsService } from 'app/services/reports.service';
import { StaffService } from 'app/services/staff.service';
import { ToastServiceService } from 'app/services/toast-service.service';
import { TrackAdminActivityService } from 'app/services/track-admin-activity.service';
import { GlobalVariable } from 'app/shared/global';
import moment from 'moment';
import { DomSanitizer } from '@angular/platform-browser';
export class Customers {
  id: number;
  name: string;
}
export class Sites {
  id: number;
  site_name: string;
}


@Component({
  selector: 'app-sign-in-out',
  templateUrl: './sign-in-out.component.html',
  styleUrls: ['../users-reports.component.scss'],
  changeDetection: ChangeDetectionStrategy.OnPush
})
export class SignInOutComponent implements OnInit, OnDestroy {


  startOfWeek: Date;
  endOfWeek: Date;
  customers: Customers[] = [];
  dateRange: any;
  signInOutData: any[] = []
  start = moment().startOf('week').add(1, 'days').toDate();
  end = moment().endOf('week').add(1, 'days').toDate();
  constructor(public globals: GlobalVariable, private cus: CustomerService,
    private toast: ToastServiceService, public dateAdapter: DateAdapter<Date>,
    private userService: StaffService, private resportService: ReportsService,
    private trackAdmin: TrackAdminActivityService, 
    private cdr: ChangeDetectorRef) {
    this.dateAdapter.setLocale('en-AU');

    let routerId = localStorage.getItem('routerId')
    if (!routerId) {
      this.activity()
    }

  }

  ngOnInit(): void {

    this.cus.getCust().subscribe(({ success, data }) => {
      if (success) {
        this.customers = data
        this.globals.selectedCustomers = data
        this.cdr.markForCheck()
      }
    })
  }

  customersIds
  sitesList: any;
  receiveDataFromChild(data: any) {
    this.customersIds = data.value.map(item => item.id);
    if (data) {
      this.userService.getCusSite(this.customersIds).subscribe(({ success, data }) => {
        if (success) {
          this.sitesList = data
          this.cdr.markForCheck()
        }
      })
    }
  }

  // selected sites 
  sitesIds;
  receiveDataFromChildSite(data: any) {
    this.sitesIds = data?.map(item => item.id);
  }


  downloadExcelFile(fileUrl: string, fileName: string) {
    const link = document.createElement('a');
    link.href = fileUrl;
    link.target = '_blank';
    link.download = fileName;
    link.click();
  }

  getStartEnd(event: any) {
    this.dateRange = event
  }


  getSignInOut(type) {
    const startDate = moment(this.dateRange.start).format('MM-DD-YYYY');
    const endDate = moment(this.dateRange.end).format('MM-DD-YYYY');
    let data = {
      date: startDate + ' - ' + endDate,
      customer_id: this.customersIds,
      sites: this.sitesIds,
      type: type
    };

    this.resportService.getSignInOutReport(data).subscribe(({ success, path, message, data }) => {
      if (success && type == 'excel') {
        this.downloadExcelFile(path, 'sign_in_out.xlxs')
      } else if (success && data && type == 'preview') {
        this.signInOutData = data
      } else {
        this.toast.toastNotification1(message, 'SignIn-Out Report!');
      }
      this.cdr.markForCheck()
    });
  }

  ngOnDestroy() {
    this.trackAdmin.storeActivity('Task Report', 'Exit Task Report Page', localStorage.getItem('routerId')).subscribe(res => {
      if (res.success) {
        localStorage.removeItem('routerId');
      }
    })
  }

  activity() {
    this.trackAdmin.storeActivity('Task Report', `Enter in Task Report Page`).subscribe(({ success, id }) => {
      if (success) {
        localStorage.setItem('routerId', id)
      }
    })
  }
}
