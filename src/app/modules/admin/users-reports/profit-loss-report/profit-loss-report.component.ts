import { Component, OnInit } from '@angular/core';
import { FormControl, FormGroup } from '@angular/forms';
import { DateAdapter } from '@angular/material/core';
import { CustomerService } from 'app/services/customer.service';
import { ReportsService } from 'app/services/reports.service';
import { ToastServiceService } from 'app/services/toast-service.service';
import { GlobalVariable } from 'app/shared/global';
import moment from 'moment';

export class Customers {
  id: number;
  name: string;
}

interface CustomerData {
  customer_name: { id: number; name: string; email: string; };
  data_from: string;
  data_to: string;
  chargerate_hours: string;
  pay_amount: number;
  pay_hours: number;
  tax: number;
  travel_time: string;
  charge_amount: number;
}

@Component({
  selector: 'app-profit-loss-report',
  templateUrl: './profit-loss-report.component.html',
  styleUrls: ['./profit-loss-report.component.scss']
})
export class ProfitLossReportComponent implements OnInit {

  startOfWeek
  endOfWeek
  customers: Customers[] = [];

  range = new FormGroup({
    start: new FormControl<Date | null>(null),
    end: new FormControl<Date | null>(null),
  });

  constructor(private cus: CustomerService, public globals: GlobalVariable, public dateAdapter: DateAdapter<Date>,
    private resportService: ReportsService, private toast: ToastServiceService,) {
    this.dateAdapter.setLocale('en-AU');
  }

  ngOnInit(): void {
    this.startOfWeek = moment().startOf('week').add(1, 'days').toDate();
    this.endOfWeek = moment().endOf('week').add(1, 'days').toDate();
    this.range.setValue({
      start: this.startOfWeek,
      end: this.endOfWeek
    });

    this.cus.getCust().subscribe(({ success, data }) => {
      if (success) {
        this.customers = data
        this.globals.selectedCustomers = data
      }
    })
  }

  customersIds
  fromMultiCustomer
  receiveDataFromChild(data: string) {
    this.fromMultiCustomer = data;
    this.customersIds = this.fromMultiCustomer.value.map(item => item.id);
  }

  previewReport: any[] = [];
  getProfitLossReport(type) {
    const startDate = moment(this.range.value.start).format('MM-DD-YYYY');
    const endDate = moment(this.range.value.end).format('MM-DD-YYYY');
    let data = {
      date: startDate + ' - ' + endDate,
      customer_id: this.customersIds,
      type: type
    };

    this.resportService.getProfitLossReports(data).subscribe(({ success, path, status, message, data, type }) => {
      if (success && type === 'excel') {
        this.downloadExcelFile(path, 'Profit/Loss Report.xlxs')
      }
      else if (success && type === 'preview') {
        this.previewReport = data;
        this.previewReport.forEach(entry => {
          entry['invoice_hours'] = this.calculateInvoiceHours(entry);
          entry['diff_in_hours'] = this.calculateDiffInHours(entry);
          entry['super_amount'] = this.calculateSuperAmount(entry.pay_amount);
          entry['net_amount'] = this.calculateNetAmount(entry.pay_amount, entry.tax);
          entry['net_pnl'] = this.calculateNetPNL(entry.charge_amount, entry.net_amount, entry['super_amount']);
        });
      }
      else {
        this.toast.toastNotification1(message, 'Profit/Loss Report!')
      }
    });

  }

  downloadExcelFile(fileUrl: string, fileName: string) {
    console.log(fileName);

    const link = document.createElement('a');
    link.href = fileUrl;
    link.target = '_blank';
    link.download = fileName;
    link.click();
  }

  calculateInvoiceHours(entry: CustomerData): number {
    return this.parseChargeRateHours(entry.chargerate_hours) + parseFloat(entry.travel_time);
  }

  calculateDiffInHours(entry: CustomerData): number {
    return this.calculateInvoiceHours(entry) - entry.pay_hours;
  }

  calculateSuperAmount(payAmount: number): number {
    const percent = 0.11;
    return payAmount * percent;
  }

  calculateNetAmount(payAmount: number, tax: number): number {
    return payAmount - tax;
  }

  calculateNetPNL(chargeAmount: number, netAmount: number, superAmount: number): number {
    return chargeAmount - netAmount + superAmount;
  }

  parseChargeRateHours(hours: string): number {
    return parseFloat(hours.replace(',', ''));
  }

}
