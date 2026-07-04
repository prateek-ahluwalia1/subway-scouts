import { Component, ElementRef, OnInit, ViewChild } from '@angular/core';
import { SiteService } from 'app/services/site.service';
import { TimeSheetService } from 'app/services/time-sheet.service';
import { FormControl } from '@angular/forms';
import { MatTableDataSource } from '@angular/material/table';
import { animate, state, style, transition, trigger } from '@angular/animations';
import moment from 'moment';
import { ToastServiceService } from 'app/services/toast-service.service';
import { GlobalVariable } from 'app/shared/global';
import { Router } from '@angular/router';
import { MatSelect } from '@angular/material/select';
import { DateAdapter, MatOption } from '@angular/material/core';
import { PageEvent } from '@angular/material/paginator';
import { Title } from '@angular/platform-browser';
import { TrackAdminActivityService } from 'app/services/track-admin-activity.service';
export interface PeriodicElement {
}

const ELEMENT_DATA: PeriodicElement[] = [
];
export class Customers {
  id: number;
  name: string;
}
export class Guards {
  id: number;
  name: string;
}
@Component({
  selector: 'app-time-sheet',
  templateUrl: './time-sheet.component.html',
  styleUrls: ['./time-sheet.component.scss'],
  animations: [
    trigger('detailExpand', [
      state('collapsed', style({ height: '0px', minHeight: '0' })),
      state('expanded', style({ height: '*' })),
      transition('expanded <=> collapsed', animate('225ms cubic-bezier(0.4, 0.0, 0.2, 1)')),
    ]),
  ],

})
export class TimeSheetComponent implements OnInit {

  dataSource = new MatTableDataSource();
  columnsToDisplay = ['Staff Name', 'Day Hours', 'Night Hours', 'Saturday', 'Sunday', 'Public Holiday', 'Total Hours'];
  expandedElement: PeriodicElement | null;
  sitesList: any = []
  state = new FormControl('');
  stateList: string[] = ['Victoria', 'New South Wales', 'Tasmania', 'Queensland', 'Western Australia', 'South Australia', 'ACT'];
  customers: Customers[] = [];
  guards: Guards[] = [];

  isPayApproved: boolean = false;
  isPayApproved2: boolean = false;
  isPayApproved3: boolean = false;
  isExpanded: boolean = false;
  searchTerm = "";
  expandedTimesheet: any = null;

  start: Date;
  end: Date;
  date = moment();
  weekno = Math.ceil(this.date.date() / 7);
  days = [];
  inputValue = '';
  customersIds: any[] = [];
  timesheet_details = [];
  fromMultiCustomer: any = {};
  guard_id = null;
  timesheets = [];
  timesheet_id = [];

  allSelected = false;
  @ViewChild('selects') selects: MatSelect;
  length: number;
  pageSize: number = 20;
  pageIndex: number = 0;
  pageSizeOptions: number[] = [5, 10, 25];

  hidePageSize = false;
  showPageSizeOptions = true;
  showFirstLastButtons = true;
  disabled = false;

  pageEvent: PageEvent;
  selectAllChecked: boolean = false;
  id;
  type;
  isShowSkelton: boolean = true

  // searchTerm: string = '';
  searchType: number

  constructor(private siteServices: SiteService, private timeSheetServices: TimeSheetService,
    private globals: GlobalVariable, public router: Router, public dateAdapter: DateAdapter<Date>,
    private toast: ToastServiceService, private titleService: Title, private global: GlobalVariable,
    private trackAdmin: TrackAdminActivityService) {
    this.dateAdapter.setLocale('en-AU');
    const activatedUrl = this.router.url;
    this.globals.ActivateUrl = activatedUrl
    const currentMoment = moment();
    this.start = currentMoment.startOf('week').toDate();
    this.end = currentMoment.endOf('week').toDate();
    this.titleService.setTitle('Sites | The scouts');


    let routerId = localStorage.getItem('routerId')
    if (!routerId) {
      this.activity()
    }
  }

  ngOnInit(): void {
    this.id = this.global.admin.admin_id
    this.type = this.global.admin.admin_user_type
    this.siteServices.getCustomer().subscribe(
      (res) => {
        if (res.success) {
          if (this.type === 'admin' || this.type === 'super-admin') {
            this.customers = res.data
          }
          else if (this.type === 'customer') {
            const loggedInCustomer = res.data.find((customer) => customer.id === this.id);
            if (loggedInCustomer) {
              this.customers = [loggedInCustomer];
            }
          }
        }
      }, (error) => {
        console.log(error)
      }
    );

  }

  ngOnDestroy() {
    this.trackAdmin.storeActivity('Time Sheet', 'Exit Time Sheet Page', localStorage.getItem('routerId')).subscribe(res => {
      if (res.success) {
        localStorage.removeItem('routerId');
      }
    })
  }

  activity() {
    this.trackAdmin.storeActivity('Time Sheet', `Enter in Time Sheet Page`).subscribe(({ success, id }) => {
      if (success) {
        localStorage.setItem('routerId', id)
      }
    })
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
  fromMultiSite
  receiveDataFromChildSite(data: string) {
    this.fromMultiSite = data;
  }

  onSubmit() {
    let data = {
      guard_id: this.guard_id,
      comment: this.inputValue
    }
    this.timeSheetServices.storeComment(data).subscribe((res) => {
      if (res.success) {
        this.inputValue = null
        let status = "Timesheet feedback";
        this.toast.toastNotification(res.message, status);
      }

    })
  }

  onExpandedChange(expandedElement: any) {
    if (expandedElement) {
      this.searchType = expandedElement.id
      this.runApi(expandedElement);
    }
  }

  selectedCardIds: number[] = [];

  arrayWithShiftId
  runApi(element) {
    this.isShowSkelton = true;
    const startDate = moment(this.start).format('MM-DD-YYYY');
    const endDate = moment(this.end).format('MM-DD-YYYY');
    if (element.shift_id) {
      this.arrayWithShiftId = this.shiftCollections.find(shiftArray => {
        return shiftArray.includes(element.shift_id);
      });
    }
    let data = {
      guard_id: element.id,
      start: startDate,
      end: endDate,
      id: element.shift_id,
      shift_collection: this.arrayWithShiftId
    }
    // console.log(data)
    this.timeSheetServices.getTimesheetDetails(data).subscribe(({ success, data }) => {
      if (success) {
        this.timesheet_details = data;
        this.isShowSkelton = false;
      }
    })
  }


  receiveDataFromChild(data: any) {
    this.fromMultiCustomer = data;
    if (Array.isArray(this.fromMultiCustomer.value)) {
      this.customersIds = this.fromMultiCustomer.value.map(item => item.id);
    }
    if (this.customersIds.length > 0) {
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

  shiftCollections;
  searchAll() {
    const startDate = moment(this.start).format('MM-DD-YYYY');
    const endDate = moment(this.end).format('MM-DD-YYYY');
    const eventValue = this.pageEvent;
    const data = {
      length: eventValue ? eventValue.length : 0,
      pageIndex: eventValue ? eventValue.pageIndex : 0,
      pageSize: eventValue ? eventValue.pageSize : this.pageSize,
      previousPageIndex: eventValue ? eventValue.previousPageIndex : 0,
      guard_id: this.guardsIds,
      start: startDate,
      end: endDate,
      state: this.state.value,
      customer_ids: this.customersIds
    };
    this.timeSheetServices.getTimesheet(data).subscribe(({ success, data, message, length }) => {
      if (success) {
        this.timesheets = data;
        this.shiftCollections = this.timesheets.map(timesheet => timesheet.shift_collection);
        ELEMENT_DATA.length = 0; // clear the existing data from the array
        const timesheetIds = this.timesheets.flatMap(timesheet => timesheet.id);
        this.timesheet_id = timesheetIds

        this.timesheets?.forEach(timesheet => {
          let guardName: string;
          if (timesheet.unprofile_name) {
            guardName = timesheet.unprofile_name;
          } else if (timesheet.first_name) {
            const nameParts = [timesheet.first_name, timesheet.middle_name, timesheet.last_name];
            guardName = nameParts.filter(Boolean).join(' ');
          }
          else {
            guardName = 'Unassign Shift';
          }
          const periodicElement: PeriodicElement = {
            'Staff Name': guardName,
            'Day Hours': timesheet.morning_hours,
            'Night Hours': timesheet.night_hours,
            'Saturday': (timesheet.saturday_morning_hours || 0) + (timesheet.saturday_night_hours || 0),
            'Sunday': (timesheet.sunday_morning_hours || 0) + (timesheet.sunday_night_hours || 0),
            'Public Holiday': (timesheet.ph_morning_hours || 0) + (timesheet.ph_night_hours || 0),
            'Total Hours': timesheet.hours,
            'id': timesheet.id,
            'shift_id': timesheet.shift_id
          };
          ELEMENT_DATA.push(periodicElement);
        });
        this.dataSource.data = ELEMENT_DATA;
        this.length = length
      } else {
        const status = "Time sheet";
        this.dataSource.data = [];
        this.timesheets = []
        this.toast.toastNotification1(message, status);
      }
    });

  }

  guardsIds
  fromMultiGuard
  receiveDataFromChildGuards(data: any) {
    this.fromMultiGuard = data;
    this.guardsIds = this.fromMultiGuard.value.map((item) => item.id);

  }

  handlePageEvent(e: PageEvent) {
    this.pageEvent = e;
    this.length = e.length;
    this.pageSize = e.pageSize;
    this.pageIndex = e.pageIndex;
    this.searchAll()
  }

  setPageSizeOptions(setPageSizeOptionsInput: string) {
    if (setPageSizeOptionsInput) {
      this.pageSizeOptions = setPageSizeOptionsInput.split(',').map(str => +str);
    }
  }

  extractedShiftCollection: any[];
  onPublishToggle(isChecked: boolean, list) {
    let toggledata = {
      guard_id: list.guard_id,
      roster_id: list.id,
      admin_id: this.globals.admin.admin_id,
    }
    this.timeSheetServices.toggleChange(toggledata).subscribe(({ success, msg }) => {
      if (success) {
        let status = "publish status"
        const startDate = moment(this.start).format('MM-DD-YYYY');
        const endDate = moment(this.end).format('MM-DD-YYYY');
        let data = {
          guard_id: list.guard_id,
          start: startDate,
          end: endDate,
          id: list.id,
          shift_collection: this.arrayWithShiftId
        }
        this.timeSheetServices.getTimesheetDetails(data).subscribe(({ success, data }) => {
          if (success) {
            this.timesheet_details = data;
            this.toast.toastNotification(msg, status);
          }
          else {
            this.toast.toastNotification1(msg, status);
          }
        })
      }
    });
  }

  convertExcel() {
    const startDate = moment(this.start).format('MM-DD-YYYY');
    const endDate = moment(this.end).format('MM-DD-YYYY');
    const eventValue = this.pageEvent;
    const data = {
      length: eventValue ? eventValue.length : 0,
      pageIndex: eventValue ? eventValue.pageIndex : 0,
      pageSize: eventValue ? eventValue.pageSize : this.pageSize,
      previousPageIndex: eventValue ? eventValue.previousPageIndex : 0,
      guard_id: this.guardsIds,
      start: startDate,
      end: endDate,
      state: this.state.value,
      customer_ids: this.customersIds
    };
    this.timeSheetServices.getResport(data).subscribe(res => {
      if (res.success && res.path) {
        window.open(res.path, '_blank');
        this.toast.toastNotification(res.message, 'Time Sheet Operation')
      } else {
        this.toast.toastNotification1(res.message, 'Time Sheet Operation')
      }

    },
      (error) => {
        this.toast.toastNotification(this.global.apiError, 'Time Sheet Operation')
      })

  }


  onStateSelect(value: string) {
    if (value) {
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

}