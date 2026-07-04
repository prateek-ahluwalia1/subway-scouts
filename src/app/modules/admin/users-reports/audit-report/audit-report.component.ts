import { ChangeDetectionStrategy, ChangeDetectorRef, Component, OnInit } from '@angular/core';
import { PermissionsService } from 'app/services/permissions.service';
import { ReportsService } from 'app/services/reports.service';
import { ToastServiceService } from 'app/services/toast-service.service';
import { TrackAdminActivityService } from 'app/services/track-admin-activity.service';
import moment from 'moment';

@Component({
  selector: 'app-audit-report',
  templateUrl: './audit-report.component.html',
  styleUrls: ['../users-reports.component.scss'],
  changeDetection: ChangeDetectionStrategy.OnPush
})
export class AuditReportComponent implements OnInit {

  auditReport: any[] = [];
  filteredAuditReport: any[] = [];
  dateRange: any;
  searchTerm = '';
  adminPermissions: any;
  start = moment().startOf('week').add(1, 'days').toDate();
  end = moment().endOf('week').add(1, 'days').toDate();

  constructor(
    private reportService: ReportsService, 
    private cdr: ChangeDetectorRef,
    private permissionService: PermissionsService,
    private trackAdmin: TrackAdminActivityService, 
    private toast: ToastServiceService
  ) {
    const per = this.permissionService.getPermissionsByTitle('Reports');
    this.adminPermissions = per?.childPage?.find(item => item.title === 'Audit Report');
  }

  ngOnInit(): void {
    this.getAuditReport();
  }

  getAuditReport() {
    const startDate = moment(this.dateRange?.start || this.start).format('YYYY-MM-DD');
    const endDate = moment(this.dateRange?.end || this.end).format('YYYY-MM-DD');
    let data = {
      start: startDate,
      end: endDate,
    }
    this.reportService.getAuditReport(data).subscribe(({ success, data }) => {
      if (success) {
        this.auditReport = data;
        this.filteredAuditReport = data;
        this.filterAuditReport();
        this.cdr.markForCheck();
      } else {
        this.toast.toastNotification1('Audit Report!', 'Data not found.');
      }
    });
  }

  filterAuditReport() {
    if (!this.searchTerm) {
      this.filteredAuditReport = this.auditReport;
    } else {
      this.filteredAuditReport = this.auditReport.filter(item => 
        (item.admin?.name.toLowerCase().includes(this.searchTerm.toLowerCase())) ||
        (item.guard_name && item.guard_name.toLowerCase().includes(this.searchTerm.toLowerCase())) ||
        (item.site?.site_name && item.site.site_name.toLowerCase().includes(this.searchTerm.toLowerCase()))
      );
    }
  }

  downloadAudit(id) {
    this.reportService.downloadAudit(id).subscribe(({ success, path }) => {
      if (success) {
        this.downloadPdf(path, `${'audit_report'}.pdf`);
        this.trackAdmin.storeActivity('Invoice Report', `Download a audit file`, localStorage.getItem('routerId')).subscribe();
      }
    });
  }

  downloadPdf(url: string, fileName: string) {
    const link = document.createElement('a');
    link.setAttribute('target', '_blank');
    link.setAttribute('href', url);
    link.setAttribute('download', fileName);
    document.body.appendChild(link);
    link.click();
    link.remove();
  }

  getStartEnd(event: any) {
    this.dateRange = event;
  }

  delAudit(id) {
    this.reportService.delAudit(id).subscribe(({ success, message }) => {
      if (success) {
        this.getAuditReport();
        this.toast.toastNotification('Audit Report!', message);
        this.trackAdmin.storeActivity('Invoice Report', `Delete a audit file`, localStorage.getItem('routerId')).subscribe();
      } else {
        this.getAuditReport();
        this.toast.toastNotification1('Audit Report!', message);
      }
    });
  }
}
