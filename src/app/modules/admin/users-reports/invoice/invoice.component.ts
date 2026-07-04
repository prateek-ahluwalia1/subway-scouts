import { ChangeDetectionStrategy, ChangeDetectorRef, Component, OnDestroy, OnInit } from '@angular/core';
import { ReportsService } from 'app/services/reports.service';
import { StaffService } from 'app/services/staff.service';
import { TrackAdminActivityService } from 'app/services/track-admin-activity.service';
import { GlobalVariable } from 'app/shared/global';
import { CustomerService } from 'app/services/customer.service';
import { DateAdapter } from '@angular/material/core';
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
  selector: 'app-invoice',
  templateUrl: './invoice.component.html',
  styleUrls: ['../users-reports.component.scss'],
  changeDetection: ChangeDetectionStrategy.OnPush
})
export class InvoiceComponent implements OnInit, OnDestroy {



  dateRange: any;
  customersIds
  fromMultiCustomer
  sitesList: any[] = []
  customers: Customers[] = [];
  previewData: any[] = []

  invoiceHeader: string[] = [
    "State", "Site Name", "Site Level", "Staff", "Staff Phone", "Customer",
    "Date", "Shift Start", "Shift End", "Sign In", "Sign Out", "Hours",
    "M-F Weekday",
    "M-F Day Rates",
    "M-F Weeknight",
    "M-F Night Rates",
    "Saturday",
    "Saturday Rates",
    "Sunday",
    "Sunday Rates",
    "Public Holiday Hours",
    "Public Holiday Rates",
    "Travel Time",
    "Total Amount",
    "Payroll",
    "Bank Name",
    "BSB",
    "Bank Account Number",
    "P.O/W.O",
    "Training"
  ];

  start = moment().startOf('week').add(1, 'days').toDate();
  end = moment().endOf('week').add(1, 'days').toDate();
  constructor(public globals: GlobalVariable, private cus: CustomerService,
    public dateAdapter: DateAdapter<Date>,
    private userService: StaffService, private resportService: ReportsService, private _commomService: CommonServiceService,
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


  receiveDataFromChild(data: string) {
    this.fromMultiCustomer = data;
    this.customersIds = this.fromMultiCustomer.value.map(item => item.id);
    if (this.fromMultiCustomer) {
      this.userService.getCusSite(this.customersIds).subscribe(({ success, data }) => {
        if (success) {
          this.sitesList = data
          this.cdr.markForCheck()
        }
      })
    }
  }

  sitesIds;
  receiveDataFromChildSite(data: any) {
    this.sitesIds = data?.map(item => item.id);
  }


  getStartEnd(event: any) {
    this.dateRange = event
  }


  getReports(type, sheet) {
    const startDate = moment(this.dateRange.start).format('MM-DD-YYYY');
    const endDate = moment(this.dateRange.end).format('MM-DD-YYYY');
    let data = {
      date: startDate + ' - ' + endDate,
      customer_id: this.customersIds,
      sites: this.sitesIds,
      type: type
    }
    this.resportService.getReports(data, sheet).subscribe(({ success, path, data }) => {

      if (success && type == 'excel') {
        type == 'pdf' ? this._commomService.downloadPdf(path, `${sheet}.pdf`) : this._commomService.downloadExcelFile(path, `${sheet}.xlxs`)
        this.trackAdmin.storeActivity('Invoice Report', `Download a ${type} file`, localStorage.getItem('routerId')).subscribe(() => {
        })
      }
      else if (success && type == 'preview') {
        this.previewData = data
      }
      this.cdr.markForCheck()
    });
  }



  ngOnDestroy() {
    this.trackAdmin.storeActivity('Exit Invoice Report Page', 'Exit Invoice Report Page', localStorage.getItem('routerId')).subscribe(res => {
      if (res.success) {
        localStorage.removeItem('routerId');
      }
    })
  }

  activity() {
    this.trackAdmin.storeActivity('Invoice Report Page', 'Enter in Invoice Report Page').subscribe(({ success, id }) => {
      if (success) {
        localStorage.setItem('routerId', id)
      }
    })
  }

  calculateTotalHours(site_id) {
    const filteredData = this.previewData.filter(item => item.site_id === site_id);
    if (filteredData.length > 0) {
      const totalHours = filteredData.reduce((accumulator, item) => {
        return accumulator + parseFloat(item.hours) + parseFloat(item.travel_time);
      }, 0);
      return totalHours;
    }
    return '';
  }
  morningHours(site_id) {
    const filteredData = this.previewData.filter(item => item.site_id === site_id);
    if (filteredData.length > 0) {
      const totalHours = filteredData.reduce((accumulator, item) => {
        return accumulator + parseFloat(item.morning_hours);
      }, 0);
      return totalHours;
    }
    return '';
  }

  totalDayRates(site_id) {
    const filteredData = this.previewData.filter(item => item.site_id === site_id);
    if (filteredData.length > 0) {
      const total = filteredData.reduce((accumulator, item) => {
        return accumulator + parseFloat(item.day_rate);
      }, 0);
      return total;
    }
    return '';
  }

  totalNightRates(site_id) {
    const filteredData = this.previewData.filter(item => item.site_id === site_id);
    if (filteredData.length > 0) {
      const total = filteredData.reduce((accumulator, item) => {
        return accumulator + parseFloat(item.night_rate);
      }, 0);
      return total;
    }
    return '';
  }


  totalAmount() {
    if (this.previewData.length > 0) {
      const totalAmount = this.previewData.reduce((accumulator, item) => {
        return accumulator + item.total_amount;
      }, 0);
      return `$ ${totalAmount.toFixed(2)}`; // Format to 2 decimal places
    }
    return `$ 0`;
  }


  saturday_night_hours(site_id) {
    const filteredData = this.previewData.filter(item => item.site_id === site_id);
    if (filteredData.length > 0) {
      const totalHours = filteredData.reduce((accumulator, item) => {
        return accumulator + parseFloat(item.saturday_night_hours);
      }, 0);
      return totalHours;
    }
    return '';
  }


  totalSaturdayHoursLocation(site_id) {
    const filteredData = this.previewData.filter(item => item.site_id === site_id);
    if (filteredData.length > 0) {
      const total = filteredData.reduce((accumulator, item) => {
        return (accumulator + parseFloat(item.saturday_morning_hours) + parseFloat(item.saturday_night_hours));
      }, 0);
      return total;
    }
    return '';
  }

  totalSaturdayRatesLocation(site_id) {
    const filteredData = this.previewData.filter(item => item.site_id === site_id);
    if (filteredData.length > 0) {
      const total = filteredData.reduce((accumulator, item) => {
        return accumulator + parseFloat(item.saturday_rate);
      }, 0);
      return total;
    }
    return '';
  }

  totalSundayHoursLocation(site_id) {
    const filteredData = this.previewData.filter(item => item.site_id === site_id);
    if (filteredData.length > 0) {
      const total = filteredData.reduce((accumulator, item) => {
        return accumulator + parseFloat(item.sunday_morning_hours) + parseFloat(item.sunday_night_hours);
      }, 0);
      return total;
    }
    return '';
  }


  totalSundayRatesLocation(site_id) {
    const filteredData = this.previewData.filter(item => item.site_id === site_id);
    if (filteredData.length > 0) {
      const total = filteredData.reduce((accumulator, item) => {
        return accumulator + parseFloat(item.sunday_rate);
      }, 0);
      return total;
    }
    return '';
  }

  totalPhHoursLocation(site_id) {
    const filteredData = this.previewData.filter(item => item.site_id === site_id);
    if (filteredData.length > 0) {
      const total = filteredData.reduce((accumulator, item) => {
        return accumulator + parseFloat(item.ph_morning_hours) + parseFloat(item.ph_night_hours);
      }, 0);
      return total;
    }
    return '';
  }

  totalPhRatesLocation(site_id) {
    const filteredData = this.previewData.filter(item => item.site_id === site_id);
    if (filteredData.length > 0) {
      const total = filteredData.reduce((accumulator, item) => {
        return accumulator + parseFloat(item.public_holiday_rate);
      }, 0);
      return total;
    }
    return '';
  }

  totalTravelTimeLocation(site_id) {
    const filteredData = this.previewData.filter(item => item.site_id === site_id);
    if (filteredData.length > 0) {
      const total = filteredData.reduce((accumulator, item) => {
        return accumulator + parseFloat(item.travel_time_value);
      }, 0);
      return total;
    }
    return '';
  }

  totalAmountLocation(site_id) {
    const filteredData = this.previewData.filter(item => item.site_id === site_id);
    if (filteredData.length > 0) {
      const total = filteredData.reduce((accumulator, item) => {
        return accumulator + parseFloat(item.total_amount);
      }, 0);
      return total;
    }
    return '';
  }


  totalNightHours(site_id) {
    const filteredData = this.previewData.filter(item => item.site_id === site_id);
    if (filteredData.length > 0) {
      const totalHours = filteredData.reduce((accumulator, item) => {
        return accumulator + parseFloat(item.night_hours);
      }, 0);
      return totalHours;
    }
    return '';
  }

  totalSaturdayMorningHours(site_id) {
    const filteredData = this.previewData.filter(item => item.site_id === site_id);
    if (filteredData.length > 0) {
      const totalHours = filteredData.reduce((accumulator, item) => {
        return accumulator + parseFloat(item.saturday_morning_hours);
      }, 0);
      return totalHours;
    }
    return '';
  }

  sunday_morning_hours(site_id) {
    const filteredData = this.previewData.filter(item => item.site_id === site_id);
    if (filteredData.length > 0) {
      const totalHours = filteredData.reduce((accumulator, item) => {
        return accumulator + parseFloat(item.sunday_morning_hours);
      }, 0);
      return totalHours;
    }
    return '';
  }

  sunday_night_hours(site_id) {
    const filteredData = this.previewData.filter(item => item.site_id === site_id);
    if (filteredData.length > 0) {
      const totalHours = filteredData.reduce((accumulator, item) => {
        return accumulator + parseFloat(item.sunday_night_hours);
      }, 0);
      return totalHours;
    }
    return '';
  }


  ph_morning_hours(site_id) {
    const filteredData = this.previewData.filter(item => item.site_id === site_id);
    if (filteredData.length > 0) {
      const totalHours = filteredData.reduce((accumulator, item) => {
        return accumulator + parseFloat(item.ph_morning_hours);
      }, 0);
      return totalHours;
    }
    return '';
  }

  ph_night_hours(site_id) {
    const filteredData = this.previewData.filter(item => item.site_id === site_id);
    if (filteredData.length > 0) {
      const totalHours = filteredData.reduce((accumulator, item) => {
        return accumulator + parseFloat(item.ph_night_hours);
      }, 0);
      return totalHours;
    }
    return '';
  }


  trackByInvoiceId(index: number, item: any): any {
    return item.id;
  }

}
