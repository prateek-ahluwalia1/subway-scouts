import { ChangeDetectorRef, Component, OnInit } from '@angular/core';
import { CustomerService } from 'app/services/customer.service';
import { ReportsService } from 'app/services/reports.service';
import { StaffService } from 'app/services/staff.service';
import { ToastServiceService } from 'app/services/toast-service.service';
import { GlobalVariable } from 'app/shared/global';
import moment from 'moment';
import { CommonServiceService } from '../common-service.service';

@Component({
  selector: 'app-incident',
  templateUrl: './incident.component.html',
  styleUrls: ['../users-reports.component.scss'],

})
export class IncidentComponent implements OnInit {
  dateRange: any;

  singleSites
  customersId
  fromMultiCustomer;
  customers: any[] = [];
  singleSite: any[] = [];
  incidentReport: any[] = []

  dataListCopy
  start = moment().startOf('week').add(1, 'days').toDate();
  end = moment().endOf('week').add(1, 'days').toDate();
  constructor(public globals: GlobalVariable, private cus: CustomerService, private resportService: ReportsService,
    private userService: StaffService, private cdr: ChangeDetectorRef, private toast: ToastServiceService, private _commonService: CommonServiceService) {


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



  selectedCus(data: string) {
    this.fromMultiCustomer = data;
    this.customersId = this.fromMultiCustomer.value.id;
    const array = [this.customersId];
    if (this.fromMultiCustomer) {
      this.userService.getCusSite(array).subscribe(res => {
        if (res.success) {
          this.singleSites = res.data
          this.cdr.markForCheck()
        }
      })
    }
  }


  receiveDataFromChildSi(data: string) {
    this.fromMultiCustomer = data;
    console.log("Site id", this.fromMultiCustomer)
    this.singleSite = this.fromMultiCustomer.id
  }

  getStartEnd(event: any) {
    this.dateRange = event
  }


  getIncidentReport() {
    const startDate = moment(this.dateRange.start).format('MM-DD-YYYY');
    const endDate = moment(this.dateRange.end).format('MM-DD-YYYY');
    let data = {
      date: startDate + ' - ' + endDate,
      site_id: this.singleSite,
    };

    this.resportService.getIncidentReport(data).subscribe(({ success, data, message }) => {
      if (success) {
        this.incidentReport = data
        this.dataListCopy = this.incidentReport
        this.cdr.markForCheck()

      } else {
        this.incidentReport = []
        this.toast.toastNotification1(message, 'Incident Report!');
      }
    });
  }


  dowloadIncidentReport(id) {
    let data = {
      incident_id: id
    }

    this.resportService.dowloadIncidentReport(data).subscribe(({ success, path, message }) => {
      if (success) {
        this._commonService.downloadPdf(path, 'incident_report.pdf')
        this.toast.toastNotification(message, 'Incident Report!')
      } else {
        this.toast.toastNotification1('Something went wrong', 'Incident Report!')
      }
    });
  }
}
