import { Component, OnInit, TemplateRef, ViewChild } from '@angular/core';
import { DateAdapter, MatOption } from '@angular/material/core';
import { TimeSheetService } from 'app/services/time-sheet.service';
import moment from 'moment';
import { CommonServiceService } from '../common-service.service';
import { GlobalVariable } from 'app/shared/global';
import { ToastServiceService } from 'app/services/toast-service.service';
import { StaffService } from 'app/services/staff.service';
import { CustomerService } from 'app/services/customer.service';
import { FormControl } from '@angular/forms';
import { MatSelect } from '@angular/material/select';
import { MatDialog, MatDialogRef } from '@angular/material/dialog';
import { NgbModal, NgbModalRef } from '@ng-bootstrap/ng-bootstrap';

export class Customers {
  id: number;
  name: string;
}

export class Guards {
  id: number;
  name: string;
}

@Component({
  selector: 'app-amg-job-tracker',
  templateUrl: './amg-job-tracker.component.html',
  styleUrls: ['./amg-job-tracker.component.scss']
})

export class AmgJobTrackerComponent implements OnInit {

  searchTerm = '';
  status: string = 'both';
  dates: any;
  staffList: any[] = []; 
  filteredStaffList: any[] = [];
  start = moment().startOf('week').add(1, 'days').toDate();
  end = moment().endOf('week').add(1, 'days').toDate();
  customers: Customers[] = [];
  stateList: string[] = ['Victoria', 'New South Wales', 'Tasmania', 'Queensland', 'Western Australia', 'South Australia', 'ACT'];
  state = new FormControl<string[]>([]);
  guards: Guards[] = [];
  allSelected = false;
  @ViewChild('selects') selects: MatSelect;
  showPreview: boolean = false;
  customersIds
  sitesList: any;
  guardsIds
  fromMultiGuard
  sitesIds;
  @ViewChild('advancedSearchModal') advancedSearchModal: TemplateRef<any>;
  modalRef: NgbModalRef;

  constructor(public dateAdapter: DateAdapter<Date>, public timeSheetServices: TimeSheetService,
    private _commonService: CommonServiceService, private globals: GlobalVariable, private toast: ToastServiceService,
    private userService: StaffService, private cus: CustomerService, private modalService: NgbModal,) { 
    this.dateAdapter.setLocale('en-AU');
  }

  ngOnInit(): void {

    this.state.setValue(this.stateList);

    this.onStateSelect(this.stateList);

    this.cus.getCust().subscribe(({ success, data }) => {
      if (success) {
        this.customers = data
        this.globals.selectedCustomers = data

        this.sitesIds = null;
        this.guardsIds = null;
        this.customersIds = data.map(item => item.id);
        this.userService.getCusSite(this.customersIds).subscribe(({ success, data }) => {
                if (success) {
                    this.sitesList = data;
                }
            });
      }
    })

    // this.getAllStaff();
  }

  getAllStaff() {
    let fromTo = `${moment(this.start).format('MM/DD/YYYY')} - ${moment(this.end).format('MM/DD/YYYY')}`;
    let data = {
      from_to: fromTo,
      type: 'preview',
      search: '',
      job_status: this.status,
      state: this.state?.value,
      customer_name: this.customersIds,
      guard_name: this.guardsIds,
      specific_sites: this.sitesIds
    }
    // console.log("Data", data)
    this.timeSheetServices.getJobTrackerReport(data).subscribe(({ success, data }) => {
      if (success) {
        this.staffList = data.results;
        this.filterStaff();
      }
    })
  }

  filterStaff() {
    const term = this.searchTerm.toLowerCase().trim();
    this.filteredStaffList = this.staffList.filter(detail => {
      const fullName = [
        detail.first_name || '',
        detail.middle_name || '',
        detail.last_name || ''
      ].join(' ').replace(/\s+/g, ' ').trim().toLowerCase();
      return fullName.includes(term);
    });
  }

  getStartEnd(event: any) {
    this.dates = event;
    this.start = event.start;
    this.end = event.end;
    // this.getAllStaff();
  }

  downloadExcel() {
    let fromTo = `${moment(this.start).format('MM/DD/YYYY')} - ${moment(this.end).format('MM/DD/YYYY')}`;
    let data = {
      from_to: fromTo,
      type: 'excel',
      search: '',
      job_status: this.status,
      state: this.state?.value,
      customer_name: this.customersIds,
      guard_name: this.guardsIds,
      specific_sites: this.sitesIds
    }
    this.timeSheetServices.getJobTrackerReport(data).subscribe(({ success, path, message }) => {
      if (success) {
        this._commonService.downloadExcelFile(path, 'Job Tracker Report.xlxs')
        this.toast.toastNotification(message, 'Job Tracker')
      } else {
        this.toast.toastNotification1(message, 'Job Tracker')
      }
    })
  }

  onPublishToggle(isChecked: boolean, list) {
    let toggledata = {
      guard_id: list.guard_id,
      roster_id: list.id,
      admin_id: this.globals.admin.admin_id,
    }
    this.timeSheetServices.toggleChange(toggledata).subscribe(({ success, msg }) => {
      if (success) {
        let status = "publish status";
        this.toast.toastNotification(msg, status);
        this.getAllStaff();
      }
    })
  }

  onSelectionChange(value) {
    this.status = value.value;
    this.getAllStaff();
  }

  receiveDataFromChild(data: any) {
    // this.customersIds = data.value.map(item => item.id);
    this.customersIds = data ? data.value.map(item => item.id) : this.customers.map(item => item.id);
    this.sitesIds = null;
    this.guardsIds = null;
    if (data) {
      this.userService.getCusSite(this.customersIds).subscribe(({ success, data }) => {
        if (success) {
          this.sitesList = data;
        }
      })
    }
  }

  onStateSelect(value: string[]) {
    if (value && value.length > 0) {
      let datas = {
        customer_id: this.customersIds,
        state: this.state?.value
      }
      this.timeSheetServices.getCustGuards(datas).subscribe(({ success, data }) => {
        if (success) {
          data?.forEach(element => {
            element.name = element.first_name + ' ' + element.last_name
          });
          this.guards = data
        }
      });
    }
  }

  toggleAllSelection() {
    if (this.allSelected) {
      this.selects.options.forEach((item: MatOption) => item.select());
      let datas = {
        customer_id: this.customersIds,
        state: this.state?.value
      }
      this.timeSheetServices.getCustGuards(datas).subscribe(({ success, data }) => {
        if (success) {
          data?.forEach(element => {
            element.name = element.first_name + ' ' + element.last_name
          });
          this.guards = data
        }
      });
    } else {
      this.selects.options.forEach((item: MatOption) => item.deselect());
    }
  }

  receiveDataFromChildGuards(data: any) {
    this.fromMultiGuard = data;
    this.guardsIds = this.fromMultiGuard.value.map((item) => item.id);
  }

  // onPreviewClick() {
  //   this.showPreview = true;
  //   this.getAllStaff();
  // }

  receiveDataFromChildSite(data: any) {
    this.sitesIds = data?.map(item => item.id);
  }

  openAdvancedSearchModal() {
    this.modalRef = this.modalService.open(this.advancedSearchModal, {
      size: 'lg',
      windowClass: 'advanced-search-dialog', 
      backdrop: 'static',
      keyboard: false
    });
  }

  applyFilters() {
    this.getAllStaff();
    // this.showPreview = true;
    this.modalRef.close();
  }

  closeModal() {
    if (this.modalRef) {
      this.modalRef.close();
    }
  }

}
