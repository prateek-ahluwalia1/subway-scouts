import { ChangeDetectorRef, Component, OnInit, ViewChild } from '@angular/core';
import { GlobalVariable } from 'app/shared/global';
import { CustomerService } from 'app/services/customer.service';
import { StaffService } from 'app/services/staff.service';
import { MatSelect, MatSelectChange } from '@angular/material/select';
import { FormArray, FormBuilder, FormGroup, Validators } from '@angular/forms';
import { ReportsService } from 'app/services/reports.service';
import { ToastServiceService } from 'app/services/toast-service.service';
import { TrackAdminActivityService } from 'app/services/track-admin-activity.service';
import { CommonServiceService } from '../common-service.service';

@Component({
  selector: 'app-monthly-report',
  templateUrl: './monthly-report.component.html',
  styleUrls: ['../users-reports.component.scss'],
  styles: [
    `
      .common-tracker-sheet {
        border-radius: 20px !important;
        padding: 15px 15px !important;
      }

      .first-table {
        margin-top: 30px
      }

      .crossButton {
        position: relative;
        display: inline-block;
        padding: 6px 15px;
        text-align: center;
        font-size: 16px;
        letter-spacing: 1px;
        text-decoration: none;
        color: #ffffff;
        background: var(--primary_color, #01A37E) !important;
        border: none;
        cursor: pointer;
        transition: ease-out 0.5s;
        -webkit-transition: ease-out 0.5s;
      }

      th,
      thead {
        background-color: #01A37E !important;
        color: #ffffff !important;
      }

      td {
        background-color: #f1f4f7 !important
      }
    `
  ]
})

export class MonthlyReportComponent implements OnInit {

  customers: any[] = [];
  fromMultiCustomer;
  fromMultiCustomerReport;
  customersId
  singleSites
  months = [
    { name: 'January', value: 1 },
    { name: 'February', value: 2 },
    { name: 'March', value: 3 },
    { name: 'April', value: 4 },
    { name: 'May', value: 5 },
    { name: 'June', value: 6 },
    { name: 'July', value: 7 },
    { name: 'August', value: 8 },
    { name: 'September', value: 9 },
    { name: 'October', value: 10 },
    { name: 'November', value: 11 },
    { name: 'December', value: 12 },
  ];
  selectedMonth: number | null = null;
  currentMonth: number = new Date().getMonth() + 1;
  @ViewChild('selects') selects: MatSelect;
  singleSite: any[] = [];
  injuryForm: FormGroup;
  showTables: boolean = false;
  guards = [];
  guardsIds;
  fromMultiGuard;
  leaveForm: FormGroup;
  contactForm: FormGroup;
  hazardsForm: FormGroup

  constructor(public globals: GlobalVariable, private cus: CustomerService, private cdr: ChangeDetectorRef, 
    private userService: StaffService, private fb: FormBuilder, private reportService: ReportsService,
    private toast: ToastServiceService, private trackAdmin: TrackAdminActivityService, private _commomService: CommonServiceService,) 
  {
    this.selectedMonth = this.currentMonth
  }

  ngOnInit(): void {
    this.cus.getCust().subscribe(({ success, data }) => {
      if (success) {
        this.customers = data
        this.globals.selectedCustomers = data
        this.cdr.markForCheck()
      }
    })

    this.getAllStaff()

    this.injuryForm = this.fb.group({
      rows: this.fb.array([this.createRow()])
    });

    this.leaveForm = this.fb.group({
      leaveRows: this.fb.array([this.createLeaveRow()])
    })

    this.contactForm = this.fb.group({
      contactRows: this.fb.array([this.createContactRow()])
    })

    this.hazardsForm = this.fb.group({
      hazardsRows: this.fb.array([this.createHazardsRow()])
    })
  }

  onMonthChange(event: MatSelectChange) {
    this.selectedMonth = event.value; 
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
    if(this.fromMultiCustomer.length > 0){
      this.showTables = true;
    }
    else {
      this.showTables = false;
    }
  }

  getAllStaff() {
    let data = {
      guard_status: '',
      pageIndex: 0,
      pageSize: 1000,
      length: 0,
    }
    this.userService.getStaff(data).subscribe(
      (res) => {
        if (res.success) {
          res.data.forEach((element) => {
            element.name = [
              element.first_name,
              element.middle_name,
              element.last_name,
            ]
            .filter(Boolean)
            .join(" ");
          });
        }
        this.guards = res.data
        this.cdr.markForCheck()
      },
      error => {
        console.log(error);
      }
    );
  }

  receiveDataFromChildGuards(data: any) {
    this.fromMultiGuard = data;
    this.guardsIds = this.fromMultiGuard.value.id;
  }

  getReports(){
    if (!this.customersId || !Array.isArray(this.fromMultiCustomer) || this.fromMultiCustomer.length === 0) {
      this.toast.toastNotification1('Monthly Report!', "Please select customer and site.");
      return; 
    }
    else {
      let data = {
        month: this.selectedMonth,
        customer_id: this.customersId,
        site_id: this.fromMultiCustomer.map((site: any) => site.id)
      }
      // console.log("Submit report", data)
      this.reportService.downloadMonthlyReport(data).subscribe(({ success, pdf_url }) => {
        if (success) {
          this._commomService.downloadPdf(pdf_url, `monthly-report.pdf`);
          this.trackAdmin.storeActivity('Monthly Report', `Download Monthly File`, localStorage.getItem('routerId')).subscribe();
        }
      });
    }
  }

  get rows(): FormArray {
    return this.injuryForm.get('rows') as FormArray;
  }

  createRow(): FormGroup {
    return this.fb.group({
      date: [''],
      staff_member: [''],
      injury_type: [''],
      days_lost: [''],
      description: [''],
      site_id: ['']
    });
  }

  addInjuryRow(): void {
    this.rows.push(this.createRow());
  }

  removeInjuryRow(index: number): void {
    this.rows.removeAt(index);
  }

  saveInjuryData(): void {
    if (Array.isArray(this.fromMultiCustomer) && this.fromMultiCustomer.length > 0) {
      this.fromMultiCustomerReport = this.fromMultiCustomer[0];
    } else {
      this.fromMultiCustomerReport = this.fromMultiCustomer;
    }
    const customerId = this.fromMultiCustomerReport;
    const updatedRows = this.injuryForm.value.rows.map((row: any) => ({
      ...row,
      site_id: customerId.id,
    }));
    this.injuryForm.patchValue({
      rows: updatedRows,
    });
    // console.log('Saved Data:', this.injuryForm.value.rows);
    const payload = {
      data: updatedRows,
    };
    this.reportService.addStaffInjuryDetails(payload).subscribe(({ success }) => {
      if (success) {
        this.toast.toastNotification('Monthly Staff Injury Report!', success);
        this.trackAdmin.storeActivity('Monthly Report', `Add Staff Injury Details`, localStorage.getItem('routerId')).subscribe();
      } 
      else {
        this.toast.toastNotification1('Monthly Staff Injury Report!', 'Something went wrong.');
      }
    });
  }

  get leaveRows(): FormArray {
    return this.leaveForm.get('leaveRows') as FormArray;
  }

  createLeaveRow(): FormGroup {
    return this.fb.group({
      start: [''],
      end: [''],
      guard_id: [''],
      description: [''],
      site_id: ['']
    });
  }

  addLeaveRow(): void {
    this.leaveRows.push(this.createLeaveRow());
  }

  removeLeaveRow(index: number): void {
    this.leaveRows.removeAt(index);
  }

  saveleaveData(): void {
    if (Array.isArray(this.fromMultiCustomer) && this.fromMultiCustomer.length > 0) {
      this.fromMultiCustomerReport = this.fromMultiCustomer[0];
    } else {
      this.fromMultiCustomerReport = this.fromMultiCustomer;
    }
    const customerId = this.fromMultiCustomerReport;
    const updatedRows = this.leaveForm.value.leaveRows.map((row: any) => ({
      ...row,
      site_id: customerId.id,
    }));
    this.leaveForm.patchValue({
      rows: updatedRows,
    });
    // console.log('Saved Data:', this.leaveForm.value.rows);
    const payload = {
      leave_request: updatedRows,
    };
    this.reportService.addStaffLeaveDetails(payload).subscribe(({ success }) => {
      if (success) {
        this.toast.toastNotification('Monthly Staff Leave Report!', success);
        this.trackAdmin.storeActivity('Monthly Report', `Add Staff Leave Details`, localStorage.getItem('routerId')).subscribe();
      } 
      else {
        this.toast.toastNotification1('Monthly Staff Leave Report!', 'Something went wrong.');
      }
    });
  }

  get contactRows(): FormArray {
    return this.contactForm.get('contactRows') as FormArray;
  }

  createContactRow(): FormGroup {
    return this.fb.group({
      name: [''],
      email: [''],
      phone: [''],
      site_id: ['']
    });
  }

  addContactRow(): void {
    this.contactRows.push(this.createContactRow());
  }

  removeContactRow(index: number): void {
    this.contactRows.removeAt(index);
  }

  savecontactData(): void {
    if (Array.isArray(this.fromMultiCustomer) && this.fromMultiCustomer.length > 0) {
      this.fromMultiCustomerReport = this.fromMultiCustomer[0];
    } else {
      this.fromMultiCustomerReport = this.fromMultiCustomer;
    }
    const customerId = this.fromMultiCustomerReport;
    const selectedMonth = this.selectedMonth;
    const year = new Date().getFullYear();
    const firstDay = new Date(year, selectedMonth - 1, 2);
    const dateString = firstDay.toISOString().slice(0, 10);
    const updatedRows = this.contactForm.value.contactRows.map((row: any) => ({
      ...row,
      site_id: customerId.id,
      date: dateString
    }));
    this.contactForm.patchValue({
      rows: updatedRows,
    });
    // console.log('Saved Data:', this.contactForm.value.rows);
    const payload = {
      point_contact: updatedRows,
    };
    this.reportService.addStaffContactDetails(payload).subscribe(({ success }) => {
      if (success) {
        this.toast.toastNotification('Monthly Staff Contact Report!', success);
        this.trackAdmin.storeActivity('Monthly Report', `Add Staff Contact Details`, localStorage.getItem('routerId')).subscribe();
      } 
      else {
        this.toast.toastNotification1('Monthly Staff Contact Report!', 'Something went wrong.');
      }
    });
  }

  get hazardsRows(): FormArray {
    return this.hazardsForm.get('hazardsRows') as FormArray;
  }

  createHazardsRow(): FormGroup {
    return this.fb.group({
      date: [''],
      staff_member: [''],
      description: [''],
      action_taken: [''],
      site_id: ['']
    });
  }

  addHazardsRow(): void {
    this.hazardsRows.push(this.createHazardsRow());
  }

  removeHazardsRow(index: number): void {
    this.hazardsRows.removeAt(index);
  }

  saveHazardsData(): void {
    if (Array.isArray(this.fromMultiCustomer) && this.fromMultiCustomer.length > 0) {
      this.fromMultiCustomerReport = this.fromMultiCustomer[0];
    } else {
      this.fromMultiCustomerReport = this.fromMultiCustomer;
    }
    const customerId = this.fromMultiCustomerReport;
    const updatedRows = this.hazardsForm.value.hazardsRows.map((row: any) => ({
      ...row,
      site_id: customerId.id,
    }));
    this.hazardsForm.patchValue({
      rows: updatedRows,
    });
    // console.log('Saved Data:', this.hazardsForm.value.rows);
    const payload = {
      near_misses: updatedRows,
    };
    this.reportService.addHazardsDetails(payload).subscribe(({ success }) => {
      if (success) {
        this.toast.toastNotification('Monthly Staff Near Misses / Hazards Report!', success);
        this.trackAdmin.storeActivity('Monthly Report', `Add Staff Near Misses / Hazards Details`, localStorage.getItem('routerId')).subscribe();
      } 
      else {
        this.toast.toastNotification1('Monthly Staff Near Misses / Hazards Report!', 'Something went wrong.');
      }
    });
  }
}
