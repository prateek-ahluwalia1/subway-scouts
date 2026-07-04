import { Component, OnInit } from '@angular/core';
import { PermissionsService } from 'app/services/permissions.service';
import { ReportsService } from 'app/services/reports.service';
import moment from 'moment';
import { CommonServiceService } from '../common-service.service';
import { ToastServiceService } from 'app/services/toast-service.service';

@Component({
  selector: 'app-guest-auth-report',
  templateUrl: './guest-auth-report.component.html',
  styleUrls: ['../users-reports.component.scss']
})
export class GuestAuthReportComponent implements OnInit {

  start = moment().startOf('week').add(1, 'days').toDate();
  end = moment().endOf('week').add(1, 'days').toDate();
  dateRange: any;
  adminPermissions: any;
  guardexceldata
  previewGuardData: any[] = []

  constructor(private permissionService: PermissionsService, private resportService: ReportsService,
    private _commonService: CommonServiceService, private toast: ToastServiceService,
  ) {
    const per = this.permissionService.getPermissionsByTitle('Reports');
    this.adminPermissions = per?.childPage?.find(item => item.title === '10 Hours Shift Report');
   }

  ngOnInit(): void {
  }

  getStartEnd(event: any) {
    this.dateRange = event;
    // console.log("Date range", this.dateRange)
  }

  getGuardExcel(type) {
    if (!this.dateRange || !this.dateRange.start || !this.dateRange.end) {
      console.error("Invalid date range");
      return;
    }

    const startDate = this.formatDate(this.dateRange.start);
    const endDate = this.formatDate(this.dateRange.end);

    this.guardexceldata = {
      type: type,
      date: `${startDate} - ${endDate}`
    }

    this.resportService.getAuthReports(this.guardexceldata).subscribe(({ success, path, message, data }) => {
      if (success && type == 'excel') {
        this._commonService.downloadExcelFile(path, 'Guest Auth Report.xlxs')
      } else if (success && type == 'preview') {
        this.previewGuardData = data
      } else {
        this.toast.toastNotification1(message, 'Guest Auth Report!');
      }
    });
  }

  formatDate(date: Date): string {
    const month = (date.getMonth() + 1).toString().padStart(2, '0'); 
    const day = date.getDate().toString().padStart(2, '0');
    const year = date.getFullYear();
    return `${month}/${day}/${year}`;
  }

}
