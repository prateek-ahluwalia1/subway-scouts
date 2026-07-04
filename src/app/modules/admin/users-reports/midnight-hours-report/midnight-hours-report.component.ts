import { Component, OnInit } from '@angular/core';
import { PermissionsService } from 'app/services/permissions.service';
import { ReportsService } from 'app/services/reports.service';
import { TrackAdminActivityService } from 'app/services/track-admin-activity.service';
import moment from 'moment';
import { CommonServiceService } from '../common-service.service';

@Component({
  selector: 'app-midnight-hours-report',
  templateUrl: './midnight-hours-report.component.html',
  styleUrls: ['../users-reports.component.scss'],
})
export class MidnightHoursReportComponent implements OnInit {

  start = moment().startOf('week').add(1, 'days').toDate();
  end = moment().endOf('week').add(1, 'days').toDate();
  dateRange: any;
  adminPermissions: any;

  constructor(private permissionService: PermissionsService,
    private trackAdmin: TrackAdminActivityService,
    private reportService: ReportsService, private _commomService: CommonServiceService,) 
  { 
    const per = this.permissionService.getPermissionsByTitle('Reports');
    this.adminPermissions = per?.childPage?.find(item => item.title === 'Midnight Hours Report');
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
    this.reportService.downloadMidnightHours(data).subscribe(({ success, path }) => {
      if (success) {
        this._commomService.downloadExcelFile(path, `midnight_hours-report.xlxs`);
        this.trackAdmin.storeActivity('Midnight Hours Report', `Download a midnight hours file`, localStorage.getItem('routerId')).subscribe();
      }
    });
  }

}
