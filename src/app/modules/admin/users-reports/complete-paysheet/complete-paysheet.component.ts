import { ChangeDetectionStrategy, ChangeDetectorRef, Component, OnInit } from '@angular/core';
import { FormControl } from '@angular/forms';
import { DateAdapter } from '@angular/material/core';
import { CustomerService } from 'app/services/customer.service';
import { ReportsService } from 'app/services/reports.service';
import { StaffService } from 'app/services/staff.service';
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
  selector: 'app-complete-paysheet',
  templateUrl: './complete-paysheet.component.html',
  styleUrls: ['../users-reports.component.scss'],
  changeDetection: ChangeDetectionStrategy.OnPush
})
export class CompletePaysheetComponent implements OnInit {
  dateRange: any;
  formFieldHelpers: string[] = [''];
  toppings = new FormControl('');
  stateList: string[] = ['Victoria', 'New South Wales', 'Tasmania', 'Queensland', 'Western Australia', 'South Australia', 'ACT'];
  customersIds
  fromMultiCustomer
  sitesList: any;
  sitesIds;

  customers: Customers[] = [];
  previewData: any[] = []

  paysheet_table = [
    "State",
    "Site Name",
    "Site Level",
    "Staff",
    "Account Holder Name",
    "Staff Phone",
    "Staff Type",
    "Customer",
    "Date",
    "Shift Start",
    "Shift End",
    "Sign In",
    "Sign Out",
    "Hours",
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
    "Total Travel Time",
    "Reimbursement",
    "Reimbursement Value",
    "Gross Amount",
    "Tax",
    "Super",
    "Net Payable",
    "Payroll",
    "Bank Name",
    "BSB",
    "Bank Account Number",
    "Site P.O/W.O",
    "Training",
    "Operation Notes"
  ]


  start = moment().startOf('week').add(1, 'days').toDate();
  end = moment().endOf('week').add(1, 'days').toDate();

  grandTax: any = 0
  total_rate = 0
  totalTravelRate = 0;
  grandSuper = 0;
  grandNetPayable = 0;
  constructor(public globals: GlobalVariable, private cus: CustomerService,
    public dateAdapter: DateAdapter<Date>,
    private userService: StaffService, private resportService: ReportsService, private _commonService: CommonServiceService,
    private trackAdmin: TrackAdminActivityService,
    private cdr: ChangeDetectorRef) {
    this.dateAdapter.setLocale('en-AU');
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

  receiveDataFromChildSite(data: any) {
    this.sitesIds = data?.map(item => item.id);
  }

  getReports(type, sheet) {
    const startDate = moment(this.dateRange.start).format('DD/MM/YYYY');
    const endDate = moment(this.dateRange.end).format('DD/MM/YYYY');
    let data = {
      date: startDate + ' - ' + endDate,
      customer_id: this.customersIds,
      sites: this.sitesIds,
      state: this.toppings.value,
      type: type
    }
    this.resportService.getReports(data, sheet).subscribe(({ success, path, data }) => {

      if (success && type == 'excel') {
        type == 'pdf' ? this._commonService.downloadPdf(path, `${sheet}.pdf`) : this._commonService.downloadExcelFile(path, `${sheet}.xlxs`)
        this.trackAdmin.storeActivity('Complete Paysheet Report', `Download a xlxs file`, localStorage.getItem('routerId')).subscribe(() => {
        })
      }
      else if (success && type == 'preview') {
        this.grandTax = 0
        this.previewData = data
        this.grandTax = this.totalGrandTax(data);  // Calculate grand tax before processing data
        console.log(this.grandTax);
        this.grandNetPayable = this.calculateGrandNetPay(data)

        this.processData(data)

      }
      this.cdr.markForCheck()
    });
  }

  getStartEnd(event: any) {
    this.dateRange = event
  }

  trackCompleteID(item: any): any {
    return item.id;
  }

  // first layer of total

  calculateStaffTotalHours(guard_id) {
    const filteredData = this.previewData.filter(item => item.guard_id === guard_id);
    if (filteredData.length > 0) {
      const totalHours = filteredData.reduce((accumulator, item) => {
        return accumulator + parseFloat(item.hours) + parseFloat(item.travel_time);
      }, 0);
      return totalHours;
    }
    return '';
  }

  getTotal(key: string, guard_id?: number): number {
    const filteredData = guard_id ? this.previewData.filter(item => item.guard_id === guard_id) : this.previewData;
    return filteredData.reduce((acc, cur) => acc + (parseFloat(cur[key]) || 0), 0);
  }

  totalSaturdayHours(guard_id) {
    const filteredData = this.previewData.filter(item => item.guard_id === guard_id);
    if (filteredData.length > 0) {
      const totalHours = filteredData.reduce((accumulator, item) => {
        return accumulator + parseFloat(item.saturday_morning_hours) + parseFloat(item.saturday_night_hours);
      }, 0);
      return totalHours;
    }
    return '';
  }

  totalSundayHours(guard_id) {
    const filteredData = this.previewData.filter(item => item.guard_id === guard_id);
    if (filteredData.length > 0) {
      const totalHours = filteredData.reduce((accumulator, item) => {
        return accumulator + parseFloat(item.sunday_morning_hours) + parseFloat(item.sunday_night_hours);
      }, 0);
      return totalHours;
    }
    return '';
  }

  totalPhHours(guard_id) {
    const filteredData = this.previewData.filter(item => item.guard_id === guard_id);
    if (filteredData.length > 0) {
      const totalHours = filteredData.reduce((accumulator, item) => {
        return accumulator + parseFloat(item.ph_morning_hours) + parseFloat(item.ph_night_hours);
      }, 0);
      return totalHours;
    }
    return '';
  }

  totalPhHoursRates(guard_id) {
    const filteredData = this.previewData.filter(item => item.guard_id === guard_id);
    if (filteredData.length > 0) {
      const total = filteredData.reduce((accumulator, item) => {
        const rate = parseFloat(item.public_holiday_rate);
        return accumulator + (isNaN(rate) ? 0 : rate);
      }, 0);
      return total.toFixed(2);
    }
    return '';
  }

  totalTravelTimeRates(guard_id) {
    const filteredData = this.previewData.filter(item => item.guard_id === guard_id);
    if (filteredData.length > 0) {
      const total = filteredData.reduce((accumulator, item) => {
        return accumulator + parseFloat(item.travel_time_value) * parseFloat(item.day_rate);
      }, 0);
      return total;
    }
    return '';
  }


  public calculateNetPay(guard: any, taxAmount: number): number {    
    const filteredData = this.previewData.filter(item => item.guard_id === guard.guard_id);
    let totalrates = 0;
    let totatlTravelRate = 0;

    if (filteredData.length > 0) {
      totalrates = filteredData.reduce((accumulator, item) => {
        totatlTravelRate += parseFloat(item.travel_time_value) * parseFloat(item.day_rate) + parseFloat(item.reimbursement_value);
        return accumulator + parseFloat(item.total_amount);
      }, 0);
      return totalrates - taxAmount;
    }

    return 0;
  }

  public totalRate(id: any): number {
    const filteredData = this.previewData.filter(item => item.guard_id === id);
    return filteredData.reduce((acc, item) => acc + (item.total_amount + item.travel_time_value * item.day_rate), 0);
  }

  public tax(id: any): string {
    const totalIncome = this.totalRate(id);
    const annualIncome = totalIncome * 26;
    let taxAmount = 0;
    if (annualIncome > 18200 && annualIncome <= 45000) {
      taxAmount = (0.19 * (annualIncome - 18200));
    } else if (annualIncome > 45000 && annualIncome <= 120000) {
      taxAmount = 5092 + 0.325 * (annualIncome - 45000);
    } else if (annualIncome > 120000 && annualIncome <= 180000) {
      taxAmount = 29467 + 0.37 * (annualIncome - 120000);
    } else if (annualIncome > 180000) {
      taxAmount = 51667 + 0.45 * (annualIncome - 180000);
    }

    return (taxAmount / 26).toFixed(2);
  }

  public super(id: any): string {
    const totalrates = this.totalRate(id);
    const superAmount = (totalrates * 0.11).toFixed(2);
    return superAmount;
  }


  public totalHoursCompletePaysheet(): number {
    return this.getTotal('hours');
  }

  main_total_saturday_hours() {
    let totalHours = 0;
    this.previewData.forEach(item => {
      totalHours += item.saturday_morning_hours + item.saturday_night_hours;
    });
    return totalHours;
  }
  main_total_sunday_hours() {
    let totalHours = 0;
    this.previewData.forEach(item => {
      totalHours += item.sunday_morning_hours + item.sunday_night_hours;
    });
    return totalHours;
  }

  main_total_ph_hours() {
    let totalHours = 0;
    this.previewData.forEach(item => {
      totalHours += item.ph_morning_hours + item.ph_night_hours;
    });
    return totalHours;
  }

  grandtotalPhHoursRates() {
    let totalHours = 0;
    const total = this.previewData.reduce((accumulator, item) => {
      totalHours = parseFloat(item.public_holiday_rate);
      return accumulator + (isNaN(totalHours) ? 0 : totalHours);
    }, 0);
    return total.toFixed(2);
  }

  grandtotalTravelTimeRates() {
    const total = this.previewData.reduce((accumulator, item) => {
      return accumulator + parseFloat(item.travel_time_value) * parseFloat(item.day_rate);
    }, 0);
    return total;
  }

  mainTotal() {
    let total = 0;
    this.previewData.forEach(item => {
      total += item.total_amount;
    });
    return total.toFixed(2);
  }

  resetTotals(): void {
    this.total_rate = 0;
    this.grandTax = 0;
    this.grandNetPayable = 0;
  }

  processData(data) {
    // this.resetTotals();
    data.forEach(item => {
      const travelRate = item.travel_time_value * item.day_rate;
      this.totalTravelRate += travelRate;
      this.total_rate += item.total_amount;
      this.updateGrandTotals(travelRate);
    });
  }

  updateGrandTotals(travelRate: number): void {
    const grossTotal = this.total_rate + this.totalTravelRate;
    this.grandSuper = (grossTotal * 0.11);
  }


  totalGrandTax(data) {
    let totalTax = 0;
    for (let i = 0; i < data.length; i++) {
        let currentItem = data[i];
        let previousItem = i > 0 ? data[i - 1] : null;;
        if (!previousItem || currentItem['guard_id'] !== previousItem['guard_id']) {
            let tax = parseFloat(this.tax(currentItem.guard_id)); 
            totalTax += tax;
        }
    }
    return totalTax.toFixed(2); 
}


public calculateGrandNetPay(data): number {
  let netpayable = 0;
  for (let i = 0; i < data.length; i++) {
    let currentItem = data[i];
    let previousItem = i > 0 ? data[i - 1] : null;
    if (!previousItem || currentItem['guard_id'] !== previousItem['guard_id']) {
      let netPay = this.calculateNetPay(currentItem, parseFloat(this.tax(currentItem.guard_id)));
      netpayable += netPay;
    }
  }
  return netpayable;
}

}
