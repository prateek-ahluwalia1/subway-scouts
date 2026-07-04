import { ChangeDetectionStrategy, ChangeDetectorRef, Component, OnDestroy, OnInit, ViewChild } from '@angular/core';
import { FormControl, FormGroup } from '@angular/forms';
import { DateAdapter, MatOption } from '@angular/material/core';
import { MatSelect } from '@angular/material/select';
import { ReportsService } from 'app/services/reports.service';
import { StaffService } from 'app/services/staff.service';
import { ToastServiceService } from 'app/services/toast-service.service';
import { TrackAdminActivityService } from 'app/services/track-admin-activity.service';
import { GlobalVariable } from 'app/shared/global';
import moment from 'moment';
import { CommonServiceService } from '../common-service.service';
export class Customers {
  id: number;
  name: string;
}
export class Sites {
  id: number;
  site_name: string;
}


@Component({
  selector: 'app-staff-report',
  templateUrl: './staff-report.component.html',
  styleUrls: ['../users-reports.component.scss'],
  changeDetection: ChangeDetectionStrategy.OnPush
})
export class StaffReportComponent implements OnInit, OnDestroy {

  states: string[] = ['Victoria', 'New South Wales', 'Tasmania', 'Queensland', 'Western Australia', 'South Australia', 'ACT'];

  state = new FormControl(null)
  allSelected = false;
  previewGuardData: any[] = []
  guardexceldata
  status
  @ViewChild('selects') selects: MatSelect;
  fromMultiGuard: any;

  constructor(public globals: GlobalVariable,
    private toast: ToastServiceService, public dateAdapter: DateAdapter<Date>,
    private userService: StaffService, private resportService: ReportsService,
    private trackAdmin: TrackAdminActivityService,
    private cdr: ChangeDetectorRef, private _commonService: CommonServiceService) {
    this.dateAdapter.setLocale('en-AU');

    let routerId = localStorage.getItem('routerId')
    if (!routerId) {
      this.activity()
    }

  }

  ngOnInit(): void {

  }

  toggleAllSelection() {
    if (this.allSelected) {
      this.selects.options.forEach((item: MatOption) => item.select());
    } else {
      this.selects.options.forEach((item: MatOption) => item.deselect());
    }
  }

  onSelectionChange(value) {
    this.status = value.value;
  }
 

  getGuardExcel(type) {
    this.guardexceldata = {
      state: this.state.value,
      guard_status: this.status,
      type: type
    }

    this.resportService.getGuardReports(this.guardexceldata).subscribe(({ success, path, message, data }) => {
      if (success && type == 'excel') {
        this._commonService.downloadExcelFile(path, 'Staff Report.xlxs')
      } else if (success && type == 'preview') {
        this.previewGuardData = data
      } else {
        this.toast.toastNotification1(message, 'Staff Report!');
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
