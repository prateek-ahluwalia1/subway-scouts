import { Component, OnInit } from '@angular/core';
import { PermissionsService } from 'app/services/permissions.service';
import { TrackAdminActivityService } from 'app/services/track-admin-activity.service';
import moment from 'moment';
import { CommonServiceService } from '../common-service.service';
import { ReportsService } from 'app/services/reports.service';

@Component({
  selector: 'app-comparison-report',
  templateUrl: './comparison-report.component.html',
  styleUrls: ['../users-reports.component.scss']
})
export class ComparisonReportComponent implements OnInit {

  start = moment().startOf('week').add(1, 'days').toDate();
  end = moment().endOf('week').add(1, 'days').toDate();
  dateRange: any;
  adminPermissions: any;

  constructor(private permissionService: PermissionsService, private trackAdmin: TrackAdminActivityService, 
    private reportService: ReportsService, private _commomService: CommonServiceService,) { 
    const per = this.permissionService.getPermissionsByTitle('Reports');
    this.adminPermissions = per?.childPage?.find(item => item.title === 'Comparison Report');
  }

  ngOnInit(): void {
  }

  getStartEnd(event: any) {
    this.dateRange = event;
  }

  getReport(){
    const startDate = moment(this.dateRange?.start || this.start).format('DD/MM/YYYY');
    const endDate = moment(this.dateRange?.end || this.end).format('DD/MM/YYYY');
    let data = {
      date: `${startDate} - ${endDate}`,
    }
    this.reportService.downloadComparisonReport(data).subscribe(({ success, path }) => {
      if (success) {
        this._commomService.downloadExcelFile(path, `comparison-report.xlxs`);
        this.trackAdmin.storeActivity('Comparison Report', `Download comparison report`, localStorage.getItem('routerId')).subscribe();
      }
    });
  }

}
