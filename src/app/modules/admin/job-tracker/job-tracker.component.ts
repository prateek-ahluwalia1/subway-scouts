import { Component, OnInit, ViewChild } from '@angular/core';
import { FormControl, FormGroup } from '@angular/forms';
import moment from 'moment';
import { GlobalVariable } from 'app/shared/global';
import { CustomerService } from 'app/services/customer.service';
import { TimeSheetService } from 'app/services/time-sheet.service';
import { ActivatedRoute, Router } from '@angular/router';
import { ToastServiceService } from 'app/services/toast-service.service';
import { JobTrackerService } from 'app/services/job-tracker.service';
import { StaffService } from 'app/services/staff.service';
import { MatSelect, MatSelectChange } from '@angular/material/select';
import { DateAdapter, MatOption } from '@angular/material/core';

export class Customers {
  id: number;
  name: string;
}
export class Sites {
  id: number;
  site_name: string;
}

@Component({
  selector: 'app-job-tracker',
  templateUrl: './job-tracker.component.html',
  styleUrls: ['./job-tracker.component.scss']
})
export class JobTrackerComponent implements OnInit {

  jobType
  selectedIndex: any;
  guards = [];
  placeholder = "";
  states: string[] = ['Victoria', 'New South Wales', 'Tasmania', 'Queensland', 'Western Australia', 'South Australia', 'ACT'];
  guardType: string[] = ['Direct', 'Contractor'];
  siteStatus: string[] = ['Active', 'Inactive'];
  dataList = []
  active: any;
  searchTerm = '';
  users = [];
  isChecked: boolean;
  guard_id: any;
  range = new FormGroup({
    start: new FormControl<Date | null>(null),
    end: new FormControl<Date | null>(null),
  });

  startOfWeek
  endOfWeek
  dataListCopy// make a copy of the original array
  slectedAll: boolean = true
  allSelected = false;
  @ViewChild('selects') selects: MatSelect;
  adminId = new FormControl(null)

  constructor(public globals: GlobalVariable, private cus: CustomerService,
    private timeSheetServices: TimeSheetService, 
    private toast: ToastServiceService, private jobTracker: JobTrackerService,
    private userService: StaffService, public router: Router, public dateAdapter: DateAdapter<Date>,) {
    this.dateAdapter.setLocale('en-AU');

    this.startOfWeek = moment().startOf('week').toDate();
    // console.log("Start Date", this.startOfWeek)
    this.endOfWeek = moment().endOf('week').toDate();
    this.range.setValue({
      start: this.startOfWeek,
      end: this.endOfWeek
    });
  }
  showPublishColumn: boolean = false;

  customers: Customers[] = [];

  ngOnInit(): void {

    this.cus.getCust().subscribe(({ success, data }) => {
      if (success) {
        this.customers = data
      }
    })
  }

  guardList: any = []
  guardsIds;
  fromMultiGuard;
  receiveDataFromGuards(data: any) {
    this.fromMultiGuard = data;
    this.guardsIds = this.fromMultiGuard.value.map(item => item.id);
  }


  getJobs() {
    let params = {
      type: this.jobType,
      start: moment(this.range.value.start).format('MM-DD-YYYY'),
      end: moment(this.range.value.end).format('MM-DD-YYYY'),
      customer_ids: this.customersIds,
      sites_ids: this.sitesIds,
      state: this.adminId.value
    }
    this.jobTracker.getJobs(params).subscribe(({ success, data, message }) => {
      if (success) {
        this.dataList = data;
        this.dataListCopy = this.dataList
        this.guardList = Array.from(new Set(this.dataList.map(obj => obj.guard_id)))
          .map(guard_id => {
            return data.find(obj => obj.guard_id === guard_id);
          });
      }
      else if (!success) {
        this.dataList = data;
        this.guardList = []
        this.toast.toastNotification1(message, 'Job Tracker!')
      }
      else {
        this.toast.toastNotification1('Something went wrong', 'Job Tracker!')
      }
    })
  }


  ////send this is id in filter
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
        }
      })
    }
  }

  // selected sites 
  sitesIds;
  receiveDataFromChildSite(data: any) {
    this.sitesIds = data?.map((item) => item.id);
  }


  selectGuard(i, id) {
    this.dataList = [...this.dataListCopy];
    const filteredData = this.dataList.filter(item => item.guard_id === id);
    this.dataList = filteredData
    this.selectedIndex = i
    if (this.selectedIndex) {
      this.slectedAll = false
    }
  }

  onPublishToggle(isChecked: boolean, index: number, list) {
    let data = {
      guard_id: list.guard_id,
      roster_id: list.id,
      admin_id: this.globals.admin.admin_id,
    }
    this.timeSheetServices.toggleChange(data).subscribe(({ success, msg}) => {
      if (success) {
        let status = "publish status"
        this.toast.toastNotification(msg, status);
      }
      this.getJobs();
    });
  }

  searchAll() {
    this.selectedIndex = -1
    this.slectedAll = true
    this.getJobs()
  }



  toggleAllSelection() {
    if (this.allSelected) {
      this.selects.options.forEach((item: MatOption) => item.select());
    } else {
      this.selects.options.forEach((item: MatOption) => item.deselect());
    }
  }
}