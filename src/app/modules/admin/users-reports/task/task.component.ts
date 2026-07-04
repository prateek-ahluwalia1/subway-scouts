import { ChangeDetectionStrategy, ChangeDetectorRef, Component, OnDestroy, OnInit } from '@angular/core';
import { FormControl, FormGroup } from '@angular/forms';
import { DateAdapter } from '@angular/material/core';
import { CustomerService } from 'app/services/customer.service';
import { ReportsService } from 'app/services/reports.service';
import { StaffService } from 'app/services/staff.service';
import { ToastServiceService } from 'app/services/toast-service.service';
import { TrackAdminActivityService } from 'app/services/track-admin-activity.service';
import { GlobalVariable } from 'app/shared/global';
import moment from 'moment';
export class Customers {
  id: number;
  name: string;
}
export class Sites {
  id: number;
  site_name: string;
}

@Component({
  selector: 'app-task',
  templateUrl: './task.component.html',
  styleUrls: ['../users-reports.component.scss'],
  changeDetection: ChangeDetectionStrategy.OnPush
})
export class TaskComponent implements OnInit, OnDestroy {
  dataListCopy
  taskReports: any = []
  guardList: any = []

  toppings = new FormControl('');

  formFieldHelpers: string[] = [''];
  isCheckedArray: boolean[] = [];
  placeholder = "";
  selectedGuardIds = []
  toppingList: string[] = ['Victoria', 'New South Wales', 'Tasmania', 'Queensland', 'Western Australia', 'South Australia', 'ACT'];
  allSelected = false;
  adminId = new FormControl(null)
  searchTerm = '';

  reportColumn
  task_report = ['Staff Name', 'Location Name', 'Shift Date', 'No. of Tasks', 'Action']

  customers: Customers[] = [];
  slectedAll: boolean = false
  selectedIndex: any;
  dataList = []

  dates: any

  start = moment().startOf('week').add(1, 'days').toDate();
  end = moment().endOf('week').add(1, 'days').toDate();
  constructor(public globals: GlobalVariable, private cus: CustomerService,
    private toast: ToastServiceService, public dateAdapter: DateAdapter<Date>,
    private userService: StaffService, private resportService: ReportsService,
    private trackAdmin: TrackAdminActivityService,
    private cdr: ChangeDetectorRef) {
    this.dateAdapter.setLocale('en-AU');

    let routerId = localStorage.getItem('routerId')
    if (!routerId) {
      this.activity()
    }

  }

  ngOnInit(): void {
    this.reportColumn = this.task_report

    this.cus.getCust().subscribe(({ success, data }) => {
      if (success) {
        this.customers = data
        this.globals.selectedCustomers = data
        this.cdr.markForCheck()
      }
    })
  }


  ////send this is id in filter
  customersIds
  sitesList: any;
  receiveDataFromChild(data: any) {
    this.customersIds = data.value.map(item => item.id);
    if (data) {
      this.userService.getCusSite(this.customersIds).subscribe(({ success, data }) => {
        if (success) {
          this.sitesList = data
          this.cdr.markForCheck()
        }
      })
    }
  }

  // selected sites 
  sitesIds;
  receiveDataFromChildSite(data: any) {
    this.sitesIds = data?.map(item => item.id);
  }

  taskReport() {
    this.getTasks()
  }

  getTasks() {
    const startDate = moment(this.dates?.start).format('MM-DD-YYYY');
    const endDate = moment(this.dates?.end).format('MM-DD-YYYY');
    let data = {
      date: startDate + ' - ' + endDate,
      customer_ids: this.customersIds,
      sites_ids: this.sitesIds,
      state: this.toppings.value,
    };

    this.resportService.getTaskReports(data).subscribe(({ success, data, message }) => {
      if (success) {
        this.guardList = []
        this.taskReports = []
        this.taskReports = data;
        this.taskReports?.sort((a, b) => {
          const nameA = a.first_name.toUpperCase();
          const nameB = b.first_name.toUpperCase();
          if (nameA < nameB) {
            return -1;
          }
          if (nameA > nameB) {
            return 1;
          }
          return 0; // Names are equal
        });

        this.guardList = this.filterUniqueData(data)
        this.dataListCopy = this.taskReports
        this.cdr.markForCheck()

      } else {
        this.toast.toastNotification1(message, 'Task Report!');
      }
    });
  }

  filterUniqueData(array) {
    const uniqueData = [];
    const seen = new Set();

    for (const item of array) {
      const key = `${item.first_name}-${item.middle_name}-${item.last_name}-${item.guard_id}`;

      if (!seen.has(key)) {
        seen.add(key);
        uniqueData.push(item);
      }
    }

    return uniqueData;
  }

  searchAll() {
    this.slectedAll = true
    this.selectedIndex = -1
    this.getTasks()
    // this.getJobs()
  }

  selectGuard(i, id) {
    this.dataList = [...this.dataListCopy];
    const filteredData = this.dataList.filter(item => item.guard_id === id);
    console.log(filteredData);
    this.taskReports = filteredData
    this.selectedIndex = i
  }

  ngOnDestroy() {
    this.trackAdmin.storeActivity('Task Report', 'Exit Task Report Page', localStorage.getItem('routerId')).subscribe(res => {
      if (res.success) {
        localStorage.removeItem('routerId');
      }
    })
  }

  activity() {
    this.trackAdmin.storeActivity('Task Report', `Enter in Task Report Page`).subscribe(({ success, id }) => {
      if (success) {
        localStorage.setItem('routerId', id)
      }
    })
  }

  trackById(item: any) {
    return item.id
  }


  dowloadTaskReport(id) {
    let data = {
      id: id
    }
    this.resportService.dowloadTaskReport(data).subscribe(({ success, path, message }) => {
      if (success) {
        this.downloadPdf(path, 'task_report.pdf')
        this.toast.toastNotification(message, 'Task Report!')
        this.cdr.markForCheck()

      }
    })
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
    this.dates = event
  }
}
