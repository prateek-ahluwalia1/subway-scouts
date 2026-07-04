import { ChangeDetectionStrategy, ChangeDetectorRef, Component, ElementRef, OnInit, Renderer2, TemplateRef, ViewChild } from '@angular/core';
import { FormBuilder, FormControl, FormGroup } from '@angular/forms';
import moment from 'moment';
import { GlobalVariable } from 'app/shared/global';
import { MatDialog } from '@angular/material/dialog';
import { faCalendar, faClock, faUser, faTrash, faCreditCard, faPencil, faXmark } from '@fortawesome/free-solid-svg-icons';
import { StaffService } from 'app/services/staff.service';
import { JobRoster1Service } from 'app/services/job-roster1.service';
import { ModalDismissReasons, NgbModal, NgbModalRef } from '@ng-bootstrap/ng-bootstrap';
import { animate, state, style, transition, trigger } from '@angular/animations';
import { MatTableDataSource } from '@angular/material/table';
import { ToastServiceService } from 'app/services/toast-service.service';
import { ConfirmBoxInitializer, DialogLayoutDisplay, AppearanceAnimation, DisappearanceAnimation, ButtonLayoutDisplay, ButtonMaker, DialogInitializer } from '@costlydeveloper/ngx-awesome-popup';
import { DateAdapter, ThemePalette } from '@angular/material/core';
import { ActivatedRoute, Router } from '@angular/router';
import { TrackAdminActivityService } from 'app/services/track-admin-activity.service';
import { PermissionsService } from 'app/services/permissions.service';
import { ViewDeletedShiftsComponent } from './view-deleted-shifts/view-deleted-shifts.component';
import { ShiftsForPublishComponent } from './shifts-for-publish/shifts-for-publish.component';
import { NgxSpinnerService } from 'ngx-spinner';
import { BroadcastJobComponent } from './broadcast-job/broadcast-job.component';
import { AddStaffOnLocationComponent } from './add-staff-on-location/add-staff-on-location.component';
import { SiteService } from 'app/services/site.service';
import { CopyShiftBulkComponent } from 'app/modules/admin/models/copy-shift-bulk/copy-shift-bulk.component';
import { AddMultipleShiftsComponent } from 'app/modules/admin/models/add-multiple-shifts/add-multiple-shifts.component';
import { CreateSiteComponent } from 'app/modules/admin/models/create-site/create-site.component';
import { CheckAvailableStaffComponent } from 'app/modules/admin/models/check-available-staff/check-available-staff.component';
import { CustomeLoaderComponent } from 'app/modules/admin/custome-loader/custome-loader.component';

export class Customers {
  id: number;
  name: string;
}

export class Sites {
  id: number;
  site_name: string;
}

export interface User {}
const USERS: User[] = [];

@Component({
  selector: 'app-job-roster',
  templateUrl: './job-roster.component.html',
  styleUrls: ['./job-roster.component.scss'],
  changeDetection: ChangeDetectionStrategy.OnPush,
  animations: [
    trigger('detailExpand', [
      state('collapsed', style({ height: '0px', minHeight: '0' })),
      state('expanded', style({ height: '*' })),
      transition('expanded <=> collapsed', animate('225ms cubic-bezier(0.4, 0.0, 0.2, 1)')),
    ]),
  ],
})

export class JobRosterComponent implements OnInit {

  searchTerm;
  searchlocation;
  // setting for portal 
  portal_setting
  data: User[] = USERS;
  dataSource: MatTableDataSource<User>[];
  usersData: User[] = [];
  loginUser: any;
  selectedType = 'active'
  selectedState = 'Victoria'
  sitesList: any = []
  sitesIds
  sitesListAdhoc: any = []
  filterdSite: any
  rosterList: any
  type = 'job-new-roster'
  isCheckedTravelTime = false; faXmark = faXmark;
  isShowCustomRatesField = false;
  isShowOverTime = false;
  faPencil = faPencil; faCreditCard = faCreditCard; faTrash = faTrash;
  calenderIcon = faCalendar; faClock = faClock; faUser = faUser;
  popover = false;
  checked = false;
  indeterminate = false;
  disabled = false;
  userType = 'location';
  toppings = new FormControl('Victoria');
  formFieldHelpers: string[] = [''];
  placeholder = "";
  states: string[] = ['Victoria', 'New South Wales', 'Tasmania', 'Queensland', 'Western Australia', 'South Australia'];
  showCalender: boolean = false
  showCalenderTwo: boolean = false
  days_hours
  shift_ids: any = []
  closeAdminCreateModal: NgbModalRef;
  color: ThemePalette = 'accent';
  checkedSlide = false;
  disabledSlide = false;
  closeResult = '';
  singleSites
  customersId
  singleSite
  customers_guards
  site_guards
  siteId
  isCopyEnabled: boolean = false
  unpublish_shift_count
  guards
  rosterName
  rosterStatus
  assignMultiShitsCus: any = []
  admin_id;
  admin_type;
  admin_data;
  routeId
  rosterPermissions: any;
  sitesListChild: any[] = [];
  isShowSkelton: boolean = true
  minDate: Date;
  rosterColor
  showStats: boolean = false
  roster_id
  AddCustomrSite: FormGroup;
  customers: Customers[] = [];
  latestData: any;
  interval: any;
  customersIds: any[] = [];
  fromMultiCustomer: any = {};
  timeTracker = moment();
  selected = 'site-week'
  @ViewChild("showTable") showTable: TemplateRef<any>;
  modalRef: NgbModalRef;
  violations: any = [];
  totalViolations: any;
  suggestion: any;

  constructor(
    public userService: StaffService,
    public globals: GlobalVariable, private _router: Router, private route: ActivatedRoute,
    public dialog: MatDialog, private permissionService: PermissionsService,
    public dateAdapter: DateAdapter<Date>, public renderer2: Renderer2,
    private rosterService: JobRoster1Service, private modalService: NgbModal, private toast: ToastServiceService,
    private trackAdmin: TrackAdminActivityService, private detectChange: ChangeDetectorRef, private spinner: NgxSpinnerService,
    private fb: FormBuilder, private siteService: SiteService,) 
    {
    this.dateAdapter.setLocale('en-AU');
    this.globals.selectedCustomers = []
    this.minDate = new Date();
  }

  ngOnInit() {
    this.loginUser = this.globals.admin.admin_user_type
    const per = this.permissionService.getPermissionsByTitle('WFM Tools');
    this.rosterPermissions = per?.childPage?.find(item => item.title === 'Staff Roster/Scheduling');
    // console.log("Roster permissions", this.rosterPermissions)

    this.route.paramMap.subscribe(params => {
      const id = params.get('id');
      this.roster_id = id
      localStorage.setItem('rosterId', id)
      if (id) {
        this.rosterService.accessRoster(id).subscribe(({ success, data, roster_name, roster_status }) => {
          if (success) {
            let routerId = localStorage.getItem('routerId')
            if (!routerId) {
              this.activity(roster_name)
            }
            this.customers = data,
            // By default all customers select
              // this.globals.selectedCustomers = data
            this.rosterName = roster_name
            this.rosterStatus = roster_status
            this.detectChange.markForCheck();
          }
        });
      } else {
        this.toast.toastNotification1('Something went wrong', 'Server Error')
      }
    });

    this.polling()

    if (JSON.parse(localStorage.getItem('jobroster'))) {
      this.portal_setting = JSON.parse(localStorage.getItem('jobroster'));
    }

    this.detectChange.markForCheck();

    //for site
    this.AddCustomrSite = this.fb.group({
      customer_id: new FormControl(''),
      site_type: new FormControl('direct'),
      site_name: new FormControl(''),
      site_description: new FormControl(''),
      site_start_date: new FormControl(''),
      site_end_date: new FormControl(''),
      job_instrcutions: new FormControl(''),
      staff_type: new FormControl('1'),
      unpublished_site: new FormControl('no'),
      site_trained: new FormControl(''),
      sos_phone: new FormControl(''),
      site_state: new FormControl(''),
      site_hours: new FormControl(''),
      po_wo: new FormControl(''),
      address: new FormControl(''),
      coordinates: new FormControl(''),
      signin_radius: new FormControl(''),
      radius_alert: new FormControl(''),
      site_level: new FormControl(''),
      payrol: new FormControl('default'),
      site_payrate_level: new FormControl(''),
      site_payrate: new FormControl(''),
      site_chargerate_level: new FormControl(''),
      site_charge_rate: new FormControl(''),
      site_break: new FormControl(''),
      site_break_payable: new FormControl(''),
      site_break_chargeable: new FormControl(''),
      break_deduction_payable: new FormControl(''),
      break_deduction_chargeable: new FormControl(''),
      welfare_call: new FormControl('no'),
      welfare_call_type: new FormControl(''),
      welfare_timing: new FormControl(''),
      green_call: new FormControl('no'),
      first_green_call: new FormControl(''),
      second_green_call: new FormControl(''),
      first_green_call_time: new FormControl(''),
      second_green_call_time: new FormControl(''),
      job_instruction_file: new FormControl(''),
      site_update_reason: new FormControl(''),
      type: new FormControl('metro'),
      site_tasks: this.fb.array([]),
      is_patrolling_site: new FormControl(false),
      monitoring_person: new FormControl(''),
      monitoring_contact: new FormControl(''),
      after_hours: new FormControl(''),
      internal_patrolling: new FormControl(false),
      external_patrolling: new FormControl(false),
      intermediate_patrolling: new FormControl(false),
      scanners: this.fb.array([]),
      keys: this.fb.array([]),
      alarm_dispatch_instruction: new FormControl(''),
      intern_no_calls: new FormControl(''),
      extern_no_calls: new FormControl(''),
      intermed_no_calls: new FormControl(''),
      intern_time_type: new FormControl(''),
      extern_time_type: new FormControl(''),
      intermed_time_type: new FormControl(''),
      intern_particular_times: this.fb.array([]),
      extern_particular_times: this.fb.array([]),
      intermed_particular_times: this.fb.array([]),
    })
  }

  receiveStartDate(data) {
    this.globals.start = data.format('MM-DD-YYYY');
  }

  receiveEndDate(data) {
    this.globals.end = data.format('MM-DD-YYYY')
    if (this.customersIds.length > 0) {
      this.sendFilterData()
    }
  }

  ////send this id in filter
  receiveDataFromChild(data: string) {
    this.fromMultiCustomer = data;
    if (Array.isArray(this.fromMultiCustomer.value)) {
      this.customersIds = this.fromMultiCustomer.value.map(item => item.id);
      if (this.customersIds) {
        this.sendFilterData()
        this.getSiteByCustomer();
      }
    }
    this.detectChange.markForCheck()
  }

  receiveDataFromChildSite(data: any) {
    this.sitesListChild = data.map(item => item.id);
    const params = {
      customer_id: this.customersIds,
      state: this.selectedState,
      start: this.globals.start,
      end: this.globals.end,
      type: this.userType,
      roster_id: this.roster_id,
      site_id: this.sitesListChild
    };
    if (this.userType == 'location') {
      params['site_type'] = this.selectedType;
    } else {
      params['guard_type'] = this.selectedType;
    }

    this.rosterService.getFilterData(params).subscribe(({ success, data, message, unpublish_shift_count, total_hours, days_hours }) => {
      if (data && success) {
        data.forEach(element => {
          this.shift_ids = element.jobRoster.map(({ roster_id }) => roster_id)
        });
      }
      else {
        this.shift_ids = []
        this.sitesList = [];
        this.sitesIds = [];
        this.sitesListChild = [];
      }
      this.isCopyEnabled = this.shift_ids.length > 0 ? true : false
      let count = {
        total_hours: total_hours,
        days_hours: days_hours
      }
      this.days_hours = data ? count : ''
      this.unpublish_shift_count = unpublish_shift_count;
      this.globals.timestamp = Date.now();
      this.isShowSkelton = false
      this.detectChange.markForCheck()
    }, error => {
      this.toast.toastNotification1(this.globals.apiError, 'Request Incomplete!');
    });
    this.detectChange.markForCheck()
  }

  // reload the current page
  reloadCurrentPage() {
    this.sendFilterData()
  }

  // calender Next and Previous
  today() {
    this.globals.getWeekDays = [];
    if (this.selected == 'site-two-weeks' || this.selected == 'staff-two-weeks') {
      this.getDays(0, 'two');
    } else {
      this.getDays(0);
    }
  }

  displayNextWeek() {
    this.globals.getWeekDays = [];
    if (this.selected == 'site-two-weeks' || this.selected == 'staff-two-weeks') {
      this.getDays(2, 'two');
    } else {
      this.getDays(1);
    }
  }

  displayPrevWeek() {
    this.globals.getWeekDays = [];
    if (this.selected == 'site-two-weeks' || this.selected == 'staff-two-weeks') {
      this.getDays(-2, 'two');
    } else {
      this.getDays(-1);
    }
  } 

  getDays(e, type?) {
    if (e == 0) {
      this.timeTracker = moment();
    } else {
      this.timeTracker.add(e, 'weeks');
    }
    var startOfWeek = this.timeTracker.clone().startOf('isoWeek');
    this.globals.start = startOfWeek.format('MM-DD-YYYY');
    var endOfWeek;
    if (type == 'two') {
      endOfWeek = this.timeTracker.clone().add(1, 'weeks').endOf('isoWeek');
      this.globals.end = endOfWeek.format('MM-DD-YYYY');
    } else {
      endOfWeek = this.timeTracker.clone().endOf('isoWeek');
      this.globals.end = endOfWeek.format('MM-DD-YYYY');
    }
    this.globals.selectedDate = `${startOfWeek.format('MMM D')} - ${endOfWeek.format('D, YYYY')}`;
    var day = startOfWeek;
    while (day.isSameOrBefore(endOfWeek)) {
      const customFormat = moment(day).format("ddd , DD/MM");
      const momentFormat = moment(day).format("MM-DD-YYYY");
      this.globals.getWeekDays.push({ customFormat, momentFormat });
      day = day.add(1, 'days');
    }
    this.sendFilterData();
    this.globals.selectedSitesIds = []
    this.detectChange.detectChanges()
    return this.globals.getWeekDays;
  }

  // end calender Next and Previous
  getUserType(userType: string) {
    this.globals.timestamp = Date.now();
    this.userType = userType;
    this.isShowSkelton = true
    this.sendFilterData()
  }

  //get data from data
  filterState(value) {
    this.selectedState = value
    if (this.customersIds) {
      this.sendFilterData()
    }
  }

  siteStatus(value) {
    this.selectedType = value;
    if (this.customersIds) {
      this.sendFilterData()
    }
  }

  viewBy(value) {
    this.globals.weekType = value
    this.selected = value
    this.showCalender = true
    if (value == 'staff-two-weeks' || value == 'site-two-weeks') {
      const formatStr = 'MM-DD-YYYY';
      const startDate = moment(this.globals.end, formatStr).format();
      console.log("start date", startDate)
      const lastDayOfWeek = moment(startDate).clone().add(7, 'days');
      this.globals.end = lastDayOfWeek.format('MM-DD-YYYY')
    }
    else if(value == 'site-week' || value == 'staff-week'){
      this.sendFilterData()
    }
  }

  ////send filter value and get filter data
  sendFilterData(): void {
    const params = {
      customer_id: this.customersIds,
      state: this.selectedState,
      start: this.globals.start,
      end: this.globals.end,
      type: this.userType,
      roster_id: this.roster_id,
    };
    if (this.userType == 'location') {
      params['site_type'] = this.selectedType;
    } else {
      params['guard_type'] = this.selectedType;
    }

    this.rosterService.getFilterData(params).subscribe(({ success, data, message, unpublish_shift_count, total_hours, days_hours }) => {
      if (data && success) {
        data.forEach(element => {
          this.shift_ids = element.jobRoster.map(({ roster_id }) => roster_id)
        });
        this.sitesList = data?.map(item => ({ id: item.id, site_name: item.site_name }));
        this.sitesIds = data?.map(item => item.id);
      }
      else {
        this.shift_ids = []
        this.sitesList = [];
        this.sitesIds = [];
        this.sitesListChild = [];
      }
      this.isCopyEnabled = this.shift_ids.length > 0 ? true : false
      let count = {
        total_hours: total_hours,
        days_hours: days_hours
      }
      this.days_hours = data ? count : ''
      this.unpublish_shift_count = unpublish_shift_count;
      this.globals.timestamp = Date.now();
      this.isShowSkelton = false
      this.detectChange.markForCheck()
    }, error => {
      this.toast.toastNotification1('There is something went wrong on fetching data. Please contact with support team.', 'Request Incomplete!');
    });
  }

  CopyShiftBulk() {
    // console.log("Send customer id", this.customersIds)
    const modalRef = this.modalService.open(CopyShiftBulkComponent, { animation: true, centered: true, windowClass: 'copyShiftBulk' });
    modalRef.componentInstance.customer_id = this.customersIds;
    modalRef.result.then(
      (result) => {
        console.log(result);
      },
      (reason) => {
        console.log("Modal Closed Reason -> ", `Dismissed ${this.getDismissReason(reason)}`)
      }
    );
  }

  //implement polling on roster
  polling() {
    // this.interval = setInterval(() => {
      const params = {
        customer_id: this.customersIds,
        state: this.selectedState,
        start: this.globals.start,
        end: this.globals.end,
        type: this.userType,
        last_update: this.globals.timestamp,
        shift_ids: this.shift_ids,
      }
      if (this.userType == 'location') {
        params['site_type'] = this.selectedType
      }
      else {
        params['guard_type'] = this.selectedType
      }
      if (this.customersIds && this.customersIds.length > 0) {
        this.rosterService.polling(params).subscribe(res => {
          if (res.deleted_roster) {
            this.sendFilterData()
          }
          this.detectChange.markForCheck()
        })
      }
    // }, 30000);
  }

  ngOnDestroy() {
    clearInterval(this.interval);
    this.globals.selectedCustomers = []
    this.globals.selectedSite = []
    localStorage.removeItem('selectedSiteForNext');

    this.trackAdmin.storeActivity('Job roster', 'Job roster Page', this.routeId).subscribe(res => {
      if (res.success) {
        localStorage.removeItem('routerId');
      }
    })
  }

  ///publishShift
  publishShift() {
    this.spinner.show()
    let params = {
      start: this.globals.start,
      end: this.globals.end,
      state: this.selectedState,
      site_type: 'active',
      customer_id: this.customersIds,
      type: this.userType,
      roster_id: this.roster_id
    }
    // this.rosterService.publishAllShift(params).subscribe(({ success, data }) => {
    //   if (success) {
        const modalRef = this.modalService.open(ShiftsForPublishComponent, { size: 'lg', animation: true });
        modalRef.componentInstance.fromParent = params;
        modalRef.result.then(
          (result) => {
            this.sendFilterData(); 
            this.closeResult = `Closed with: ${result}`;
          },
          (reason) => {
            this.closeResult = `Dismissed ${this.getDismissReason(reason)}`;
          },
        );
        this.spinner.hide()
    //   }
    // })
  }

  private getDismissReason(reason: any): string {
    if (reason === ModalDismissReasons.ESC) {
      return 'by pressing ESC';
    } else if (reason === ModalDismissReasons.BACKDROP_CLICK) {
      return 'by clicking on a backdrop';
    } else {
      return `with: ${reason}`;
    }
  }

  close(data?) {
    this.modalService.dismissAll(data);
  }

  ///Site Bulk Action Perform
  siteActionBulk(type) {
    const newConfirmBox = new ConfirmBoxInitializer();
    newConfirmBox.setTitle('Confirm Action');
    newConfirmBox.setMessage('Are you sure you want to confirm this action?');

    // Choose layout color type
    newConfirmBox.setConfig({
      layoutType: DialogLayoutDisplay.SUCCESS, // SUCCESS | INFO | NONE | DANGER | WARNING
      animationIn: AppearanceAnimation.SWING, // BOUNCE_IN | SWING | ZOOM_IN | ZOOM_IN_ROTATE | ELASTIC | JELLO | FADE_IN | SLIDE_IN_UP | SLIDE_IN_DOWN | SLIDE_IN_LEFT | SLIDE_IN_RIGHT | NONE
      animationOut: DisappearanceAnimation.BOUNCE_OUT, // BOUNCE_OUT | ZOOM_OUT | ZOOM_OUT_WIND | ZOOM_OUT_ROTATE | FLIP_OUT | SLIDE_OUT_UP | SLIDE_OUT_DOWN | SLIDE_OUT_LEFT | SLIDE_OUT_RIGHT | NONE
      allowHtmlMessage: true,
      buttonPosition: 'right', // optional 
    });

    newConfirmBox.setButtonLabels('Confirm', 'Decline');
    // Simply open the popup and observe button click
    newConfirmBox.openConfirmBox$().subscribe(resp => {
      if (resp.success) {
        this.rosterService.rosterActions(type, this.customersIds, this.userType, this.roster_id, this.globals.selectedSitesIds).subscribe(({ message, success, suggestion, total_violations, violations}) => {
          if (success) {
            this.sendFilterData()
            this.globals.selectedSitesIds = []
            this.toast.toastNotification(message, 'Roster Operation!')
          }
          else {
            if(message == "Work limitation violations detected"){
            this.spinner.hide()
            this.showViolationModal(suggestion, total_violations, violations);
          }
          else {
            this.toast.toastNotification1(message, 'Roster Operation!')
          }
          }
          this.detectChange.detectChanges()
        }, (error => {
          this.toast.toastNotification1('Something went wrong. Please contact with support team.', 'Backend Error!')
        }))
      }
    });
  }

  private showViolationModal(suggestion:any, total_violations:any, violations:[]) {
    this.modalRef = this.modalService.open(this.showTable, {
      centered: true,              
      size: 'lg',                   
      backdrop: 'static',          
    });
    this.violations = violations || [];
    this.totalViolations = total_violations || 0;
    this.suggestion = suggestion || '';
  }

  siteActionBulkMultiple() {
    const modalRef = this.modalService.open(AddMultipleShiftsComponent, { animation: true, centered: true, windowClass: 'copyShiftBulk' });
    modalRef.result.then(
      (result) => {
        console.log(result);
      },
      (reason) => {
        if (reason == 'update') {
          this.sendFilterData()
        }
        console.log("Modal Closed Reason -> ", `Dismissed ${this.getDismissReason(reason)}`)
      }
    );
  }

  addGuardOnSites(model) {
    const modelRef = this.modalService.open(model, {
      windowClass:
        "add-staff-document-class guard-for-site-modal",
      animation: true,
    });
    modelRef.componentInstance.customers = this.customers
    this.customers_guards = []
    this.site_guards = [],
    this.checkedSlide = false;
  }

  ////send this id in filter
  selectedCus(data: string) {
    this.fromMultiCustomer = data;
    this.customersId = this.fromMultiCustomer.value.id;
    const array = [this.customersId];
    if (this.fromMultiCustomer) {
      this.userService.getCusSite(array).subscribe(res => {
        if (res.success) {
          this.singleSites = res.data
        }
      })
    }
  }

  receiveDataFromChildSi(data: string) {
    this.fromMultiCustomer = data;
    this.singleSite = this.fromMultiCustomer?.id
    if (this.singleSite) {
      this.getGuadSiteCustomer()
    }
  }

  getGuadSiteCustomer() {
    this.rosterService.getGuadSiteCustomer(this.singleSite, this.customersIds).subscribe(({ customers_guards, success, site_guards }) => {
      if (success) {
        this.site_guards = site_guards
        this.customers_guards = customers_guards
      }
      else {
        this.site_guards = []
        this.customers_guards = []
      }
    })
  }

  toggleGuard(event, guard, action?) {
    let guard_ids
    if (action && action == 'add_to_site_at_once') {
      guard_ids = this.customers_guards ? this.customers_guards.map(id => id.id) : ''
      if (!event.checked) {
        action = 'remove_to_site_at_once'
        guard_ids = this.site_guards ? this.site_guards.map(id => id.id) : ''

      }
      this.rosterService.addGuard(guard_ids, this.singleSite, action).subscribe(({ success, msg }) => {
        if (success) {
          if (this.singleSite) {
            this.getGuadSiteCustomer()
          }
          this.toast.toastNotification(msg, 'Add Staff On Site')
        }
      })
    }
    else {
      this.rosterService.addGuard(guard.id, this.singleSite, action).subscribe(({ success, msg }) => {
        if (success) {
          if (this.singleSite) {
            this.getGuadSiteCustomer()
          }
          this.toast.toastNotification(msg, 'Add Staff On Site')
        }
      })
    }
  }

  onTabChange(event) {
    const selectedTabIndex = event.index
    if (selectedTabIndex === 1) {
      this.checkedSlide = true
    } else {
      this.checkedSlide = false
    }
  }

  //open model for create site
  createSite() {
    this.siteService.addSite(this.AddCustomrSite.value).subscribe(({ success, id, message }) => {
      const status = 'Location Operation!';
      if (success) {
        this.siteId = id;

        const modalRef = this.modalService.open(CreateSiteComponent, { size: 'xl', backdrop: 'static' });
        modalRef.componentInstance.patrolsiteId = this.siteId;
        modalRef.componentInstance.routeId = this.routeId;
        modalRef.result.then(
          (result) => {
            console.log("Modal Result", `Closed with: ${result}`);
          },
          (reason) => {
            let rea = this.getDismissReason(reason)
            if (rea == 'update') {
              // this.getAllSites()
            }
          }
        );
      }
      else {
        this.toast.toastNotification(message, status);
      }
    },
    (error) => {
      this.toast.toastNotification('Something went wrong. Please contact the support team.', 'Request Incomplete!');
    });
  }

  AdhocShift() {
    const modalRef = this.modalService.open(BroadcastJobComponent, { size: 'lg' });
    modalRef.componentInstance.customers = this.customers
    modalRef.result.then(
      (result) => {
        `Closed with: ${result}`;
      },
      (reason) => {
        `Dismissed ${this.getDismissReason(reason)}`;
      },
    );
  }

  activity(roster_name) {
    this.trackAdmin.storeActivity('Job roster', `Enter in Job Roster ${roster_name}`).subscribe(({ success, id }) => {
      if (success) {
        localStorage.setItem('routerId', id)
        this.routeId = id
      }
    })
  }

  checkAvailableStaff() {
    const modalRef = this.modalService.open(CheckAvailableStaffComponent, {
      size: 'lg', // Set the size to large
      centered: true, // Center the modal vertically
      backdropClass: 'modal-no-backdrop',
      backdrop: 'static',
      windowClass: "modal-right-staff-avail",  // Apply custom CSS class for no backdrop
    });
    modalRef.componentInstance.modalRef = modalRef;
    modalRef.result.then(
      (result) => {
        this.closeResult = `Closed with: ${result}`;
      },
      (reason) => {
        this.closeResult = `Dismissed ${this.getDismissReason(reason)}`;
      },
    );
  }

  downloadPdf(url: string, fileName: string) {
    const link = document.createElement('a');
    // link.setAttribute('target', '_blank');
    link.setAttribute('href', url);
    link.setAttribute('download', fileName);
    document.body.appendChild(link);
    link.click();
    link.remove();
  }

  generateRosterReportNormal(type) {
    let start = moment(this.globals.start).format('YYYY-MM-DD')
    // let end = moment(this.globals.end).format('YYYY-MM-DD')
    let newEnd = moment(this.globals.end)
    let endiS = newEnd.add(1, 'day')
    let end = endiS.format("YYYY-MM-DD");
    const id = localStorage.getItem('rosterId');
    let site_ids
    this.sitesListChild.length > 0 ? site_ids = this.sitesListChild : site_ids = this.sitesList.map(item => item.id)

    let data = {
      start: start,
      end: end,
      report: type,
      customers: this.customersIds,
      roster_id: id,
      site_id: site_ids
    }

    this.rosterService.generateRosterReportNormal(data).subscribe(({ success, path, message }) => {
      if (success) {
        this.downloadPdf(path, '')
        this.toast.toastNotification(message, 'Roster Report!')
      } else {
        this.toast.toastNotification1('Something went wrong', 'Roster Report!')
      }
    });
  }

  generateRosterReport(type){

    let start = moment(this.globals.start).format('YYYY-MM-DD')
    // let end = moment(this.globals.end).format('YYYY-MM-DD')
    let newEnd = moment(this.globals.end)
    let endiS = newEnd.add(1, 'day')
    let end = endiS.format("YYYY-MM-DD");
    const id = localStorage.getItem('rosterId');
    let site_ids
    this.sitesListChild.length > 0 ? site_ids = this.sitesListChild : site_ids = this.sitesList.map(item => item.id)

    let data = {
      start: start,
      end: end,
      report: type,
      customers: this.customersIds,
      roster_id: id,
      site_id: site_ids
    }

    this.rosterService.generateRosterReport(data).subscribe(({ success, path, message }) => {
      if (success) {
        this.downloadPdf(path, '')
        this.toast.toastNotification(message, 'Roster Report!')
      } else {
        this.toast.toastNotification1('Something went wrong', 'Roster Report!')
      }
    });
  }

  getSiteByCustomer(): void {
    // const customersIds = this.fromMultiCustomer?.value?.map(item => item.id);
    // this.userService.getCusSite(customersIds).subscribe(res => {
    //   this.sitesList = res?.data
    //   const sitesList = this.sitesListChild?.map(item => ({ id: item.id, site_name: item.site_name }));
    //   this.globals.selectedSite = sitesList;
    // })
  }

  viewDeletedShifts() {
    const id = localStorage.getItem('rosterId')
    const dialogPopup = new DialogInitializer(ViewDeletedShiftsComponent);
    dialogPopup.setConfig({
      width: '800px',
      layoutType: DialogLayoutDisplay.SUCCESS // SUCCESS | INFO | NONE | DANGER | WARNING
    });

    dialogPopup.setCustomData({ id: id });
    dialogPopup.setConfig({
      width: '800px',
      loaderComponent: CustomeLoaderComponent,
      layoutType: DialogLayoutDisplay.SUCCESS // SUCCESS | INFO | NONE | DANGER | WARNING
    });
    dialogPopup.setButtons([
      new ButtonMaker('Close', 'close', ButtonLayoutDisplay.LIGHT),
      // new ButtonMaker('Download PDF', 'submit', ButtonLayoutDisplay.SUCCESS),
      // new ButtonMaker('Download CSV', 'csv', ButtonLayoutDisplay.INFO)
    ]);
    dialogPopup.openDialog$().subscribe(resp => {
    });
  }

  rosterStats() {
    this.showStats = !this.showStats
    this.rosterColor = JSON.parse(localStorage.getItem('color'))
  }
  filterKeys(obj: any): string[] {
    return Object.keys(obj).filter(key => key !== 'background_color' && key !== 'primary_color' && key !== 'secondary_color' && key !== 'unpublish_site_shifts');
  }

  @ViewChild('scrollContainer') scrollContainer!: ElementRef;

  scrollToTop() {
    this.scrollContainer.nativeElement.scrollTo({
      top: 0,
      behavior: 'smooth'
    });
  }

  sitesDelete() {
    const newConfirmBox = new ConfirmBoxInitializer();
    newConfirmBox.setTitle('Confirm Action');
    newConfirmBox.setMessage('Are you sure you want to confirm this action?');

    newConfirmBox.setConfig({
      layoutType: DialogLayoutDisplay.SUCCESS, // SUCCESS | INFO | NONE | DANGER | WARNING
      animationIn: AppearanceAnimation.SWING, // BOUNCE_IN | SWING | ZOOM_IN | ZOOM_IN_ROTATE | ELASTIC | JELLO | FADE_IN | SLIDE_IN_UP | SLIDE_IN_DOWN | SLIDE_IN_LEFT | SLIDE_IN_RIGHT | NONE
      animationOut: DisappearanceAnimation.BOUNCE_OUT, // BOUNCE_OUT | ZOOM_OUT | ZOOM_OUT_WIND | ZOOM_OUT_ROTATE | FLIP_OUT | SLIDE_OUT_UP | SLIDE_OUT_DOWN | SLIDE_OUT_LEFT | SLIDE_OUT_RIGHT | NONE
      allowHtmlMessage: true,
      buttonPosition: 'right', 
    });

    newConfirmBox.setButtonLabels('Confirm', 'Decline');
    // Simply open the popup and observe button click
    newConfirmBox.openConfirmBox$().subscribe(resp => {
      if (resp.success) {
        const sendData = {
          selected_shift_id: this.globals.selectedCardIds.map(id => ({ id })),
          reason: "N/A",
          admin_id: this.globals.admin.admin_id

        };
        this.rosterService.deleteMultipleShifts(sendData).subscribe(({ message, success }) => {
          if (success) {
            this.sendFilterData()
            this.globals.selectedCardIds = [];
            this.globals.shift_paste = false;
            this.globals.isContextMenuDisabled = false;
            this.toast.toastNotification(message, 'Roster Operation!')
          }
          else {
            this.toast.toastNotification(message, 'Roster Operation!')
          }
          this.detectChange.detectChanges()
        }, 
        (error => {
          this.toast.toastNotification1('Something went wrong. Please contact with support team.', 'Backend Error!')
        }))
      }
    });
  }
}


