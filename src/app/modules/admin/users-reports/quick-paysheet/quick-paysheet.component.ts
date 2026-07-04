import { ChangeDetectionStrategy, ChangeDetectorRef, Component, OnInit } from '@angular/core';
import { CustomerService } from 'app/services/customer.service';
import { ReportsService } from 'app/services/reports.service';
import { StaffService } from 'app/services/staff.service';
import { ToastServiceService } from 'app/services/toast-service.service';
import { TrackAdminActivityService } from 'app/services/track-admin-activity.service';
import moment from 'moment';
export class Customers {
  id: number;
  name: string;
}
@Component({
  selector: 'app-quick-paysheet',
  templateUrl: './quick-paysheet.component.html',
  styleUrls: ['../users-reports.component.scss'],
  changeDetection: ChangeDetectionStrategy.OnPush,
  styles:[

    `
     tfoot > tr > td{
  background-color: #d1d1d1
}

    `
  ]
})
export class QuickPaysheetComponent implements OnInit {

  secondTableHeaderNames: string[] = [
    "Name",
    "BSB",
    "A/C",
    "Tax File - ABN",
    "Phone",
    "Day Hours",
    "Day Pay",
    "Night Hours",
    "Night Pay",
    "Saturday Hours",
    "Saturday Pay",
    "Sunday Hours",
    "Sunday Pay",
    "Public Holiday Hours",
    "Public Holiday Pay",
    "Total Hours",
    "Gross Amount",
    "Tax",
    "Super",
    "Total Payable"
  ];

  quickPaysheets: any[] = []

  customers: Customers[] = [];
  dateRange: any;
  start = moment().startOf('week').add(1, 'days').toDate();
  end = moment().endOf('week').add(1, 'days').toDate();

  grandTotal = 0;
  grandSuper = 0;
  grossGrand = 0;
  grandTax = 0;
  grandHours = 0;

  constructor(private resportService: ReportsService, private toast: ToastServiceService, private cdr: ChangeDetectorRef,
    private userService: StaffService, private trackAdmin: TrackAdminActivityService, private cus: CustomerService) {

  }

  ngOnInit(): void {
    this.cus.getCust().subscribe(({ success, data }) => {
      if (success) {
        this.customers = data
        this.cdr.markForCheck()
      }
    })

  }

  quickPaysheet(type) {
    const startDate = moment(this.dateRange.start).format('MM-DD-YYYY');
    const endDate = moment(this.dateRange.end).format('MM-DD-YYYY');
    let data = {
      date: startDate + ' - ' + endDate,
      type: type,
      customer_ids: this.customersIds,
    }
    this.resportService.generateQuickPaysheetReport(data).subscribe(({ success, path, message, data }) => {
      if (success && type == 'preview') {
        const dataArray = Object.values(data); // Convert object values to an array
        this.sortQuickPaysheetsByName(dataArray);
        this.calculateTotals();
      } else if (success && !data) {
        this.downloadExcelFile(path, 'quick_paysheet.xlxs');
        this.toast.toastNotification(message, 'Quick Paysheet!');
        this.trackAdmin.storeActivity('Quick Paysheet Report', `Download a excel file`, localStorage.getItem('routerId')).subscribe();
      }
      this.cdr.markForCheck();
    });
  }

  sortQuickPaysheetsByName(data) {
    this.quickPaysheets = data.sort((a, b) => {
      const nameA = a.name.toLowerCase();
      const nameB = b.name.toLowerCase();
      if (nameA < nameB) return -1;
      if (nameA > nameB) return 1;
      return 0;
    });
  }

  downloadExcelFile(fileUrl: string, fileName: string) {
    const link = document.createElement('a');
    link.href = fileUrl;
    link.target = '_blank';
    link.download = fileName;
    link.click();
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

  customersIds
  fromMultiCustomer
  sitesList: any;
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


  calculateQuickTax(annualIncome: number): number {
    let tax: number;

    if (annualIncome <= 18200) {
      tax = 0;
    } else if (annualIncome > 18200 && annualIncome <= 45000) {
      tax = (0.19 * (annualIncome - 18200)) / 26;
    } else if (annualIncome > 45000 && annualIncome <= 120000) {
      tax = (5092 + 0.325 * (annualIncome - 45000)) / 26;
    } else if (annualIncome > 120000 && annualIncome <= 180000) {
      tax = (29467 + 0.37 * (annualIncome - 120000)) / 26;
    } else {
      tax = (51667 + 0.45 * (annualIncome - 180000)) / 26;
    }

    return tax;
  }

  numberFormat(val) {
    if (val != 0) {
      return `${val.toFixed(2)}`
    } else {
      return '-'
    }
  }
  numberFormatPay(val) {
    if (val != 0) {
      return '$' + `${val.toFixed(2)}`
    } else {
      return '-'
    }
  }
  getStartEnd(event: any) {
    this.dateRange = event
  }

  totalDayHours(): string {
    return this.quickPaysheets.reduce((acc, cur) => acc + (cur.day || 0), 0);
  }

  getTotalDayPay(): number {
    return this.quickPaysheets.reduce((acc, cur) => acc + (cur.day_pay || 0), 0);
  }

  getTotalNightHours(): number {
    return this.quickPaysheets.reduce((total, item) => total + (item.night || 0), 0);
  }

  getTotalNightPay(): number {
    return this.quickPaysheets.reduce((total, item) => total + (item.night_pay || 0), 0);
  }

  getTotalSaturdayHours(): number {
    return this.quickPaysheets.reduce((total, item) => total + (item.saturday || 0), 0);
  }

  getTotalSaturdayPay(): number {
    return this.quickPaysheets.reduce((total, item) => total + (item.saturday_pay || 0), 0);
  }

  getTotalSundayHours(): number {
    return this.quickPaysheets.reduce((total, item) => total + (item.sunday || 0), 0);
  }

  getTotalSundayPay(): number {
    return this.quickPaysheets.reduce((total, item) => total + (item.sunday_pay || 0), 0);
  }

  getTotalPhHours(): number {
    return this.quickPaysheets.reduce((total, item) => total + (item.ph || 0), 0);
  }

  getTotalPhPay(): number {
    return this.quickPaysheets.reduce((total, item) => total + (item.ph_pay || 0), 0);
  }

  getTotalHours(): number {
    return this.quickPaysheets.reduce((total, item) => total + (item.total_hours || 0), 0);
  }

  getTotalGross(): number {
    return this.quickPaysheets.reduce((total, item) => total + (item.day_pay + item.night_pay + item.saturday_pay + item.sunday_pay + item.ph_pay), 0);
  }

  getGrandTax(): number {
    return this.quickPaysheets.reduce((total, item) => {
      const annualIncome = (item.day_pay + item.night_pay + item.saturday_pay + item.sunday_pay + item.ph_pay) * 26;
      return total + this.calculateTax(annualIncome);
    }, 0);
  }

  calculateTotals() {
    this.quickPaysheets.forEach(pi => {
      const totalRate = pi.day_pay + pi.night_pay + pi.saturday_pay + pi.sunday_pay + pi.ph_pay;
      const annualIncome = totalRate * 26;
      let tax = this.calculateTax(annualIncome);
      let superContribution = totalRate * 0.11;

      this.grandTotal += totalRate - tax;
      this.grandSuper += superContribution;
      this.grandHours += pi.total_hours;
      this.grandTax += tax;
      this.grossGrand += totalRate;
    });
  }

  calculateTax(annualIncome: number): number {
    if (annualIncome <= 18200) {
      return 0;
    } else if (annualIncome > 18200 && annualIncome <= 45000) {
      return (0.19 * (annualIncome - 18200)) / 26;
    } else if (annualIncome > 45000 && annualIncome <= 120000) {
      return (5092 + 0.325 * (annualIncome - 45000)) / 26;
    } else if (annualIncome > 120000 && annualIncome <= 180000) {
      return (29467 + 0.37 * (annualIncome - 120000)) / 26;
    } else {
      return (51667 + 0.45 * (annualIncome - 180000)) / 26;
    }
  }
}
