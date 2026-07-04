import { animate, state, style, transition, trigger } from '@angular/animations';
import { ChangeDetectionStrategy, ChangeDetectorRef, Component, OnInit } from '@angular/core';
import { DateAdapter, ThemePalette } from '@angular/material/core';
import { MatDialog } from '@angular/material/dialog';
import { ActivatedRoute, Router } from '@angular/router';
import { ModalDismissReasons, NgbModal } from '@ng-bootstrap/ng-bootstrap';
import { RunsheetrosterService } from 'app/services/runsheetroster.service';
import { ToastServiceService } from 'app/services/toast-service.service';
import { GlobalVariable } from 'app/shared/global';
import moment from 'moment';
import { NgxSpinnerService } from 'ngx-spinner';
import { Subscription } from 'rxjs';
import { CreateNewRunsheetComponent } from '../models/create-new-runsheet/create-new-runsheet.component';
import { AppearanceAnimation, ButtonLayoutDisplay, ButtonMaker, ConfirmBoxInitializer, DialogInitializer, DialogLayoutDisplay, DisappearanceAnimation } from '@costlydeveloper/ngx-awesome-popup';
import { CopyShiftBulkComponent } from '../models/copy-shift-bulk/copy-shift-bulk.component';
import { CustomeLoaderComponent } from '../custome-loader/custome-loader.component';
import { ViewDeletedShiftsComponent } from '../operations-modules/components/job-roster/view-deleted-shifts/view-deleted-shifts.component';
import { ShiftsForPublishComponent } from '../operations-modules/components/job-roster/shifts-for-publish/shifts-for-publish.component';

export class Customers {
  id: number;
  name: string;
}

export class Sites {
  id: number;
  site_name: string;
}

@Component({
  selector: 'app-runsheet-job-roster',
  templateUrl: './runsheet-job-roster.component.html',
  styleUrls: ['./runsheet-job-roster.component.scss'],
  changeDetection: ChangeDetectionStrategy.OnPush,
  animations: [
    trigger('detailExpand', [
      state('collapsed', style({ height: '0px', minHeight: '0' })),
      state('expanded', style({ height: '*' })),
      transition('expanded <=> collapsed', animate('225ms cubic-bezier(0.4, 0.0, 0.2, 1)')),
    ]),
  ],
})
export class RunsheetJobRosterComponent implements OnInit {

  private subscription: Subscription;

  rosterName
  rosterStatus
  userType = 'run_sheet';
  isShowSkelton: boolean = true;
  selected = 'site-week';
  customers: Customers[] = [];
  sitesList: any = []
  type = 'runsheet-job-roster';
  sitesListChild: any[] = [];
  selectedType = 'active';
  showCalender: boolean = false;
  isCopyEnabled: boolean = false;
  loginUser: any;
  sitesIds
  days_hours
  unpublish_shift_count
  checkedSlide = false;
  searchTerm;
  searchlocation;
  color: ThemePalette = 'accent';
  checked = false;
  shift_ids: any = [];

  constructor(public globals: GlobalVariable, private _router: Router, private route: ActivatedRoute,
    public dialog: MatDialog, public dateAdapter: DateAdapter<Date>, private detectChange: ChangeDetectorRef,
    private roster: RunsheetrosterService, private toast: ToastServiceService, private modalService: NgbModal,
    private spinner: NgxSpinnerService,) {

    this.globals.selectedCustomers = []
    this.subscription = this.roster.publishShiftData$.subscribe(() => {
      this.sendFilterData();
    });
  }

  ngOnInit(): void {

    this.loginUser = this.globals.admin.admin_user_type

    this.route.paramMap.subscribe(params => {
      const id = params.get('id');
      localStorage.setItem('runsheet_rosterId', id)
      if (id) {
        this.roster.accessRoster(id).subscribe(({ success, data, roster_name, roster_status }) => {
          if (success) {
            this.customers = data,
              this.globals.selectedCustomers = data
            this.rosterName = roster_name
            this.rosterStatus = roster_status
            this.detectChange.markForCheck();
          }
        });
      } else {
        this.toast.toastNotification1('Something went wrong', 'Server Error')
      }
    });

    this.detectChange.markForCheck();

  }

  getUserType(userType: string) {
    this.globals.timestamp = Date.now();
    this.userType = userType;
    this.isShowSkelton = true
    this.sendFilterData()
  }

  receiveStartDate(data) {
    this.globals.start = data.format('MM-DD-YYYY');
  }

  receiveEndDate(data) {
    this.globals.end = data.format('MM-DD-YYYY')
    if (this.customersIds) {
      this.sendFilterData()
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

  displayNextWeek() {
    this.globals.getWeekDays = [];
    if (this.selected == 'site-two-weeks' || this.selected == 'staff-two-weeks') {
      this.getDays(2, 'two');
    } else {
      this.getDays(1);
    }
  }

  today() {
    this.globals.getWeekDays = [];
    if (this.selected == 'site-two-weeks' || this.selected == 'staff-two-weeks') {
      this.getDays(0, 'two');
    } else {
      this.getDays(0);
    }
  }

  timeTracker = moment();
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

  customersIds: any = null;
  fromMultiCustomer: any = {};
  receiveDataFromChild(data: string) {
    this.fromMultiCustomer = data;
    if (Array.isArray(this.fromMultiCustomer.value) && this.fromMultiCustomer.value.length > 0) {
      const selectedCustomerId = this.fromMultiCustomer.value[0].id;
      if (selectedCustomerId) {
        this.customersIds = selectedCustomerId;
        this.sendFilterData();
      }
    }
    this.detectChange.markForCheck()
  }


  receiveDataFromChildSite(data: any) {
    this.sitesListChild = data.map(item => item.id);
    console.log("Select sites data", this.sitesListChild)
    const params = {
      customer_id: this.customersIds,
      start: this.globals.start,
      end: this.globals.end,
      type: this.userType,
      run_sheet_roster_id: parseInt(localStorage.getItem('runsheet_rosterId')),
      run_sheet_id: this.sitesListChild
    };
    if (this.userType === 'run_sheet') {
      params['run_sheet_type'] = this.selectedType;
    } else {
      params['guard_type'] = this.selectedType;
    }

    this.roster.getFilterData(params).subscribe(({ success, data, message, unpublish_shift_count, total_hours, days_hours }) => {

      if (data && success) {
        data.forEach(element => {
          this.shift_ids = element.RunSheetJobRoster.map(({ roster_id }) => roster_id)
        });
      }
      else {

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
    this.detectChange.markForCheck()
  }

  viewBy(value) {
    this.globals.weekType = value
    this.selected = value
    this.showCalender = true
    if (value == 'staff-two-weeks' || value == 'site-two-weeks') {
      const formatStr = 'MM-DD-YYYY';
      const startDate = moment(this.globals.end, formatStr).format();
      const lastDayOfWeek = moment(startDate).clone().add(7, 'days');
      this.globals.end = lastDayOfWeek.format('MM-DD-YYYY')
    }
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

  reloadCurrentPage() {
    this.sendFilterData()
  }

  addGuardOnSites(model) {
    this.modalService.open(model, {
      windowClass:
        "add-staff-document-class guard-for-site-modal",
      animation: true,
    });
    this.customers_guards = []
    this.site_guards = [],
      this.checkedSlide = false;
  }

  sendFilterData(): void {
    const params = {
      customer_id: this.customersIds,
      start: this.globals.start,
      end: this.globals.end,
      type: this.userType,
      run_sheet_roster_id: parseInt(localStorage.getItem('runsheet_rosterId'))
    };
    if (this.userType === 'run_sheet') {
      params['run_sheet_type'] = this.selectedType;
    } else {
      params['guard_type'] = this.selectedType;
    }

    console.log("Filter data", params)

    this.roster.getFilterData(params).subscribe(({ success, data, message, unpublish_shift_count, total_hours, days_hours }) => {

      if (data && success) {
        data.forEach(element => {
          this.shift_ids = element.RunSheetJobRoster.map(({ roster_id }) => roster_id)
        });
        if (this.userType === 'run_sheet') {
          this.sitesList = data?.map(item => ({ id: item.run_sheet_id, site_name: item.run_sheet_title }));
          this.sitesIds = data?.map(item => item.run_sheet_id);
        }
        else {
          this.sitesList = data?.map(item => ({ id: item.guard_id, site_name: `${item.first_name} ${item.middle_name ? item.middle_name + ' ' : ''}${item.last_name}` }));
          this.sitesIds = data?.map(item => item.guard_id);
        }
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

  close(data?) {
    this.modalService.dismissAll(data);
  }

  singleSites
  customersId
  selectedCus(data: string) {
    this.fromMultiCustomer = data;
    this.customersId = this.fromMultiCustomer.value.id;
    console.log("selected customer", this.customersId)
    // const array = [this.customersId];
    if (this.fromMultiCustomer) {
      this.roster.getCusSite(this.customersId).subscribe(res => {
        if (res.success) {
          this.singleSites = res.data
        }
      })
    }
  }

  singleSite
  receiveDataFromChildSi(data: string) {
    this.fromMultiCustomer = data;
    this.singleSite = this.fromMultiCustomer?.id
    console.log("selected cust", this.singleSite)
    if (this.singleSite) {
      this.getGuadSiteCustomer()
    }
  }

  customers_guards
  site_guards
  getGuadSiteCustomer() {
    this.roster.getGuadSiteCustomer(this.singleSite, this.customersIds).subscribe(({ customers_guards, success, run_sheet_guards }) => {
      if (success) {
        this.site_guards = run_sheet_guards
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
      this.roster.addGuard(guard_ids, this.singleSite, action).subscribe(({ success, msg }) => {
        if (success) {
          if (this.singleSite) {
            this.getGuadSiteCustomer()
          }
          this.toast.toastNotification(msg, 'Add Staff On Site')
        }
      })
    }
    else {
      this.roster.addGuard(guard.id, this.singleSite, action).subscribe(({ success, msg }) => {
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

  ngOnDestroy() {
    this.globals.selectedCustomers = []
    this.globals.selectedSite = []
    this.subscription.unsubscribe();
  }

  closeResult = '';
  publishShift() {
    this.spinner.show()
    let params = {
      start: this.globals.start,
      end: this.globals.end,
      site_type: 'active',
      customer_id: this.customersIds,
      type: this.userType,
      run_sheet_roster_id: parseInt(localStorage.getItem('runsheet_rosterId'))
    }
    this.roster.publishAllShift(params).subscribe(({ success, data }) => {
      if (success) {
        const modalRef = this.modalService.open(ShiftsForPublishComponent, { size: 'lg', animation: true });
        modalRef.componentInstance.fromParent = data;
        modalRef.componentInstance.rosterType = 'run_sheet';
        this.roster.publishShiftData(data);
        modalRef.result.then(
          (result) => {
            this.closeResult = `Closed with: ${result}`;
          },
          (reason) => {
            this.closeResult = `Dismissed ${this.getDismissReason(reason)}`;
          },
        );
        this.spinner.hide()
      }
    })
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

  createSite() {
    const modalRef = this.modalService.open(CreateNewRunsheetComponent, { size: 'xl' });
    modalRef.componentInstance.fromParent = 'new';
    modalRef.result.then(
      (result) => {
        console.log("Modal Result", `Closed with: ${result}`);
      },
      (reason) => {
        let rea = this.getDismissReason(reason)
        if (rea == 'update') {
        }
      }
    );
  }

  viewDeletedShifts() {
    const id = localStorage.getItem('runsheet_rosterId')
    const dialogPopup = new DialogInitializer(ViewDeletedShiftsComponent);
    dialogPopup.setConfig({
      width: '800px',
      layoutType: DialogLayoutDisplay.SUCCESS // SUCCESS | INFO | NONE | DANGER | WARNING

    });

    dialogPopup.setCustomData({ id: id, name: 'run_sheet' });
    dialogPopup.setConfig({
      width: '800px',
      loaderComponent: CustomeLoaderComponent,
      layoutType: DialogLayoutDisplay.SUCCESS // SUCCESS | INFO | NONE | DANGER | WARNING
    });
    dialogPopup.setButtons([
      new ButtonMaker('Close', 'close', ButtonLayoutDisplay.LIGHT),
      new ButtonMaker('Download PDF', 'submit', ButtonLayoutDisplay.SUCCESS),
    ]);
    dialogPopup.openDialog$().subscribe(resp => {
      console.log('dialog response: ', resp);
    });
  }

  CopyShiftBulk() {
    const modalRef = this.modalService.open(CopyShiftBulkComponent, { animation: true, centered: true, windowClass: 'copyShiftBulk' });
    modalRef.componentInstance.fromRunsheet = 'run_sheet';
    modalRef.result.then(
      (result) => {
        console.log(result);
      },
      (reason) => {
        console.log("Modal Closed Reason -> ", `Dismissed ${this.getDismissReason(reason)}`)
      }
    );
  }

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
        // console.log("Type", type,  "CustomerIds", this.customersIds, "UserType", this.userType, "Runsheets", this.sitesListChild, "Roster Id", parseInt(localStorage.getItem('runsheet_rosterId')))
        this.roster.rosterActions(type, this.customersIds, this.userType, this.sitesListChild, parseInt(localStorage.getItem('runsheet_rosterId'))).subscribe(({ message, success }) => {
          if (success) {
            this.sendFilterData()
            this.globals.selectedSitesIds = []
            this.toast.toastNotification(message, 'Roster Operation!')
          }
          else {
            this.toast.toastNotification(message, 'Roster Operation!')
          }
          this.detectChange.detectChanges()
        }, (error => {
          this.toast.toastNotification1('Something went wrong. Please contact with support team.', 'Backend Error!')
        }))
      }
    });
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

    console.log("generate report", data);

    // this.roster.generateRosterReportNormal(data).subscribe(({ success, path, message }) => {
    //   if (success) {
    //     this.downloadPdf(path, '')
    //     this.toast.toastNotification(message, 'Roster Report!')
    //   } else {
    //     this.toast.toastNotification1('Something went wrong', 'Roster Report!')
    //   }
    // });
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

}
