import { Component, Input, OnChanges, OnInit, SimpleChanges } from '@angular/core';
import { SiteService } from 'app/services/site.service';
import { ToastServiceService } from 'app/services/toast-service.service';
import { GlobalVariable } from 'app/shared/global';

@Component({
  selector: 'app-export',
  templateUrl: './export.component.html',
  styleUrls: ['./export.component.scss']
})
export class ExportComponent implements OnInit, OnChanges {
  selectedSites: any[] = []

  @Input() dataArray: any[];
  @Input() customerIds: number[] = [];
  allSites: any[] = [];
  @Input() siteType: string = 'all';
  constructor(private toast: ToastServiceService, private siteService: SiteService, private global: GlobalVariable) { }

  ngOnInit(): void {
    this.fetchAllSites();
  }

  // ngOnChanges(changes: SimpleChanges) {
  //   if (changes['dataArray']) {
  //   }
  // }
  ngOnChanges(changes: SimpleChanges) {
    if (changes['customerIds'] || changes['dataArray'] || changes['siteType']) {
      this.fetchAllSites();
    }
  }

  fetchAllSites() {
    const params: any = {
      pageSize: 10000, 
      status: this.siteType 
    };

    if (this.customerIds && this.customerIds.length > 0) {
      params.customer_ids = this.customerIds;
    }

    this.siteService.getAllSites(params).subscribe(
      ({ success, data }) => {
        if (success) {
          this.allSites = data; // Populate the dropdown with all sites
        } else {
          this.toast.toastNotification1('Failed to load locations.', 'Request Incomplete!');
        }
      },
      (error) => {
        this.toast.toastNotification1(this.global.apiError, 'Request Incomplete!');
      }
    );
  }


  receiveDataFromChildSite(data: any) {
    this.selectedSites = data.map(item => item.id);
  }

  generateSiteLocationReport() {
    if (this.selectedSites && this.selectedSites.length > 0) {
      const params = {
        sites: this.selectedSites
      };
      this.siteService.generateSiteLocationReport(params).subscribe(({ success, path, message }) => {
        if (success) {
          this.downloadExcelFile(path)
        }
      }, error => {
        this.toast.toastNotification1(this.global.apiError, 'Request Incomplete!');
      });
    }
    else {
      this.toast.toastNotification1('Please select at least one location for report', 'Request Incomplete!');
    }
  }


  downloadExcelFile(fileUrl: string) {
    const link = document.createElement('a');
    link.href = fileUrl;
    link.target = '_blank';
    link.download = 'Locations';
    link.click();
  }
}
