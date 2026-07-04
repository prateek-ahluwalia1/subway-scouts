import { ChangeDetectorRef, Component, Input, OnInit, TemplateRef, ViewChild } from '@angular/core';
import { ModalDismissReasons, NgbModal } from '@ng-bootstrap/ng-bootstrap';
import { RunsheetrosterService } from 'app/services/runsheetroster.service';
import { GlobalVariable } from 'app/shared/global';
import moment from 'moment';
import { Subject, filter, takeUntil } from 'rxjs';
import { CreateNewRunsheetComponent } from '../../models/create-new-runsheet/create-new-runsheet.component';
import { RunsheetJobRosterComponent } from '../runsheet-job-roster.component';
import { FormBuilder, FormControl, FormGroup, Validators } from '@angular/forms';
import { CdkDragDrop } from '@angular/cdk/drag-drop';
import { AppearanceAnimation, ButtonLayoutDisplay, ButtonMaker, ConfirmBoxInitializer, DialogLayoutDisplay, DisappearanceAnimation } from '@costlydeveloper/ngx-awesome-popup';
import { ToastServiceService } from 'app/services/toast-service.service';
import { UpdateTimeComponent } from '../../models/update-time/update-time.component';
import { RunsheetAdvanceShiftComponent } from '../runsheet-advance-shift/runsheet-advance-shift.component';
import AircallPhone from 'aircall-everywhere';
import { PatrollingService } from 'app/services/patrolling.service';
import { SiteShiftsComponent } from '../../site-shifts/site-shifts.component';
import { MatDialog } from '@angular/material/dialog';
import { faTrash, faCreditCard, faPencil, faPhone, faMessage } from '@fortawesome/free-solid-svg-icons';
import { CommonOneTwoService } from '../../operations-modules/components/job-roster/common-one-two.service';

@Component({
  selector: 'app-runsheet-two-week',
  templateUrl: './runsheet-two-week.component.html',
  styleUrls: ['./runsheet-two-week.component.scss']
})
export class RunsheetTwoWeekComponent implements OnInit {

  showMobile: boolean = false;
  searchSites;
  selectedSitesIds: number[] = [];
  sites = [];
  private _unsubscribeAll: Subject<any> = new Subject<any>();
  private _data: any[];
  originalArray: any[] = [];
  @Input('calenderType') calenderType;
  @Input('rosterStatus') rosterStatus;
  siteid;
  @ViewChild('deletecontent') deletecontent: TemplateRef<any>;
  loginUser;
  AddDeleteReason: FormGroup;
  job_new_roster = [];
  shift_paste: boolean = false;
  edit_shift_side = false;
  isContextMenuDisabled: boolean = false;
  shift_select: boolean = true;
  selectedCardIds: number[] = [];
  job_id_match;
  shiftEdited;
  currentDay: any;
  clickedboxDate1;
  private aircallPhone: AircallPhone;
  @ViewChild('sendMsg') sendMsg: TemplateRef<any>;
  sdMessage: FormGroup;
  phone = faPhone;
  msg = faMessage;
  site_id_match;
  buit_in_template = [];
  faPencil = faPencil;
  faCreditCard = faCreditCard;
  faTrash = faTrash;
  popover = false;


  constructor(public global: GlobalVariable, private roster: RunsheetrosterService, private changeDetect: ChangeDetectorRef,
    private modalService: NgbModal, public runsheet_roster: RunsheetJobRosterComponent, private fb: FormBuilder,
    private toast: ToastServiceService, private commonService: CommonOneTwoService, private patrolling: PatrollingService,
    public dialog: MatDialog,) { }

  ngOnInit(): void {

    this.global.getWeekDays = [];
    var currentDate = moment();
    var weekStart = currentDate.clone().startOf('isoWeek');
    this.global.start = weekStart.format('MM-DD-YYYY');
    var weekEnd = currentDate.clone().add(1, 'weeks').endOf('isoWeek');
    this.global.end = weekEnd.format('MM-DD-YYYY');
    this.global.selectedDate = `${weekStart.format('MMM D')} - ${weekEnd.format('D, YYYY')}`;
    for (var i = 0; i <= 13; i++) {
      const customFormat = moment(weekStart).add(i, 'days').format("ddd , DD/MM");
      const momentFormat = moment(weekStart).add(i, 'days').format("MM-DD-YYYY");
      this.global.getWeekDays.push({ customFormat, momentFormat });
    };

    this.sites = []
    this.roster.sites$
      .pipe(filter(res => res && res.data),
        takeUntil(this._unsubscribeAll))
      .subscribe((sites: any) => {
        if (this._data) {
          this.sites = sites?.data.filter(item => this._data.includes(item.run_sheet_id));
        } else {
          this.sites = sites.data;
          this.originalArray = sites.data;
          // this.global.selectedSite = sites?.data.map(item => ({ id: item.id, site_name: item.run_sheet_title }));
        }
        this.sites.sort((a, b) => a.run_sheet_title.localeCompare(b.run_sheet_title));
        this.changeDetect.markForCheck();
      });

    this.loginUser = this.global.admin.admin_user_type;

    this.AddDeleteReason = this.fb.group({
      reason: new FormControl('', Validators.required),
    })

    this.sdMessage = this.fb.group({
      msg: new FormControl('', Validators.required),
    })

  }

  sideMobile() {
    this.showMobile = !this.showMobile;
  }

  trackBySiteId(index: number, site: any): number {
    return site.run_sheet_id;
  }

  ngOnDestroy(): void {
    this.roster.clearSitesData();
    this._unsubscribeAll.next(null);
    this._unsubscribeAll.complete();
  }

  modalRef
  siteAction(type, id) {
    if (type == 'edit') {
      if (this.calenderType != 'guard') {
        const modalRef = this.modalService.open(CreateNewRunsheetComponent, { size: 'xl' });
        modalRef.componentInstance.fromParent = id;
        modalRef.result.then(
          (result) => {
            console.log("Modal Result", `Closed with: ${result}`);
          },
          (reason) => {
            let rea = this.getDismissReason(reason)
            if (rea == 'update') {
              this.runsheet_roster.sendFilterData()
            }
          }
        );
      }
    }
    else if (type == 'delete') {
      this.siteid = id;
      this.modalRef = this.modalService.open(this.deletecontent);
    }
    else {
      const index = this.selectedSitesIds.indexOf(id);
      if (index > -1) {
        this.selectedSitesIds.splice(index, 1);
        if (this.selectedSitesIds.length == 0) {
        }
      } else {
        this.selectedSitesIds.push(id);
      }
      this.global.selectedSitesIds = this.selectedSitesIds
    }
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

  drop_data;
  drop(event: CdkDragDrop<string[]>) {
    if (this.rosterStatus == 'active' && this.loginUser != 'customer') {
      console.log("Event data", event)
      this.openConfirmBox('drop', event)
      event.item.data.date = event.container.id
      this.job_new_roster.push(event.item.data)
    }
    else {
      const newConfirmBox = new ConfirmBoxInitializer();
      newConfirmBox.setTitle('Access Denied');
      newConfirmBox.setMessage('This roster have not access to copy or drop');

      // Choose layout color type
      newConfirmBox.setConfig({
        layoutType: DialogLayoutDisplay.DANGER, // SUCCESS | INFO | NONE | DANGER | WARNING
        animationIn: AppearanceAnimation.ZOOM_IN_ROTATE, // BOUNCE_IN | SWING | ZOOM_IN | ZOOM_IN_ROTATE | ELASTIC | JELLO | FADE_IN | SLIDE_IN_UP | SLIDE_IN_DOWN | SLIDE_IN_LEFT | SLIDE_IN_RIGHT | NONE
        animationOut: DisappearanceAnimation.BOUNCE_OUT, // BOUNCE_OUT | ZOOM_OUT | ZOOM_OUT_WIND | ZOOM_OUT_ROTATE | FLIP_OUT | SLIDE_OUT_UP | SLIDE_OUT_DOWN | SLIDE_OUT_LEFT | SLIDE_OUT_RIGHT | NONE
        allowHtmlMessage: true,
        buttonPosition: 'center', // optional 
      });

      newConfirmBox.setButtons([
        new ButtonMaker('Discard', 'Discard', ButtonLayoutDisplay.INFO),
      ]);

      // Simply open the popup and observe button click
      newConfirmBox.openConfirmBox$().subscribe(resp => {
        if (resp.clickedButtonID == 'Discard') {
          this.runsheet_roster.sendFilterData()
        }
      });
    }
  }

  openConfirmBox(id, event?) {
    const newConfirmBox = new ConfirmBoxInitializer();
    if (id == "drop") {
      newConfirmBox.setTitle('Copy & Drop');
      newConfirmBox.setMessage('What do you want to do?');
    }
    else {
      newConfirmBox.setTitle('Confirm Action');
      newConfirmBox.setMessage('Are you sure you want to confirm this action?');
    }

    // Choose layout color type
    newConfirmBox.setConfig({
      layoutType: DialogLayoutDisplay.CUSTOM_TWO, // SUCCESS | INFO | NONE | DANGER | WARNING
      animationIn: AppearanceAnimation.FADE_IN, // BOUNCE_IN | SWING | ZOOM_IN | ZOOM_IN_ROTATE | ELASTIC | JELLO | FADE_IN | SLIDE_IN_UP | SLIDE_IN_DOWN | SLIDE_IN_LEFT | SLIDE_IN_RIGHT | NONE
      animationOut: DisappearanceAnimation.ZOOM_OUT_ROTATE, // BOUNCE_OUT | ZOOM_OUT | ZOOM_OUT_WIND | ZOOM_OUT_ROTATE | FLIP_OUT | SLIDE_OUT_UP | SLIDE_OUT_DOWN | SLIDE_OUT_LEFT | SLIDE_OUT_RIGHT | NONE
      allowHtmlMessage: true,
      buttonPosition: 'center', // optional 
    });

    if (id == 'drop') {
      newConfirmBox.setButtons([
        new ButtonMaker('Copy', 'copy', ButtonLayoutDisplay.SUCCESS),
        new ButtonMaker('Drop', 'drop', ButtonLayoutDisplay.INFO),
        new ButtonMaker('Discard', 'Discard', ButtonLayoutDisplay.DANGER)
      ]);
    }
    else {
      newConfirmBox.setButtons([
        new ButtonMaker('Confirm', 'Confirm', ButtonLayoutDisplay.SUCCESS),
        new ButtonMaker('Discard', 'Discard', ButtonLayoutDisplay.INFO),
      ]);
    }

    // Simply open the popup and observe button click
    newConfirmBox.openConfirmBox$().subscribe(resp => {
      if (resp.clickedButtonID == 'copy') {
        event.item.data.admin_id = this.global.admin.admin_id
        this.dragDropData(event)
      }
      else if (resp.clickedButtonID == 'drop') {
        event.item.data.type = 'drop'
        event.item.data.admin_id = this.global.admin.admin_id
        this.dragDropData(event)
      }
      else if (resp.clickedButtonID == 'Discard') {
        this.runsheet_roster.sendFilterData()
      }
    });
  }

  dragDropData(event) {
    event.admin_id = this.global.admin.admin_id
    let object = this.global.AddTimeDate(event.item.data.start_time, event.item.data.end_time, event.container.id)
    event.item.data.start = object.start
    event.item.data.end = object.end
    this.dragDropApi(event.item.data)
  }

  dragDropApi(data) {
    console.log("Drop", data)
    // this.spinner.show()
    this.roster.dragDrop(data).subscribe(res => {
      let status = 'Runsheet Job Roster Operation!'
      if (res.success) {
        this.toast.toastNotification(res.message, status)
        this.runsheet_roster.sendFilterData()
        this.global.timestamp = Date.now();
        if (data.type == 'drop') {
          console.log('drop with out conflict');
          // this.trackAdmin.storeActivity('Job Roster', `Drop shift  ${data.first_name + ' ' + data.last_name} to ${data.start}`, this.jobroster.routeId).subscribe(res => {
          // })
        }
        else {
          // this.trackAdmin.storeActivity('Job Roster', `Copy shift  ${data.first_name + ' ' + data.last_name} to ${data.start}`, this.jobroster.routeId).subscribe(res => {
          // })
        }
        //     this.spinner.hide()
      }
      else {
        this.addWithConflict(res, data, 'copy')
        this.global.timestamp = Date.now();
      }
    }, (error => {

    }))
  }

  addWithConflict(res, value, type?) {
    const newConfirmBox = new ConfirmBoxInitializer();
    let layoutType
    layoutType = res.msg !== 'Sorry Staff On Leave!' ? DialogLayoutDisplay.SUCCESS : DialogLayoutDisplay.DANGER;

    let buttons: ButtonMaker[] = [];

    if (res.hide) {
      newConfirmBox.setTitle('Access Denied!');
      newConfirmBox.setMessage(res.message);
      layoutType = DialogLayoutDisplay.DANGER;
      buttons.push(new ButtonMaker('Discard', 'Discard', ButtonLayoutDisplay.DANGER));
    } else {
      newConfirmBox.setTitle('Confirm Action');
      newConfirmBox.setMessage(res.message);
      buttons = [
        new ButtonMaker('Confirm', 'Confirm', ButtonLayoutDisplay.SUCCESS),
        new ButtonMaker('Discard', 'Discard', ButtonLayoutDisplay.DANGER)
      ];
    }

    newConfirmBox.setConfig({
      layoutType,
      animationIn: AppearanceAnimation.ZOOM_IN_ROTATE,
      animationOut: DisappearanceAnimation.ZOOM_OUT_WIND,
      allowHtmlMessage: true,
      buttonPosition: 'center',
    });
    newConfirmBox.setButtons(buttons);

    newConfirmBox.openConfirmBox$().subscribe(resp => {
      if (resp.clickedButtonID == 'Confirm') {
        if (type === 'saveRoster' || type === 'paste' || type === 'copy') {
          value.shift_confirm = 'yes';
          if (type === 'saveRoster') this.saveRoster(value);
          else if (type === 'paste') this.savePaste(value);
          else if (type === 'copy') this.dragDropApi(value);
        }
      } else {
        // this.spinner.hide();
        this.runsheet_roster.sendFilterData();
      }
    });
  }

  saveRoster(value): void {
    // this.spinner.show();
    const isAdmin = this.global.admin.admin_id;
    const rosterId = localStorage.getItem('runsheet_rosterId');
    value.admin = isAdmin;
    value.run_sheet_roster_id = rosterId;
    this.roster[('addShift')](value).subscribe(res => {
      const status = 'Runsheet Job Roster Operation';
      this.handleRosterResponse(res, status, value);
    }, (error) => {
      // this.spinner.hide();
      this.toast.toastNotification1('Something went wrong. Please contact the support team.', 'Request Incomplete');
    });
  }

  savePaste(data) {
    console.log("Submit copy data", data)
    // this.spinner.show()
    this.roster.copyShift(data).subscribe(res => {
      let status = 'Runsheet Job Roster Operation!'
      if (res.success) {
        this.toast.toastNotification(res.message, status)
        this.runsheet_roster.sendFilterData()
        this.global.timestamp = Date.now();
        this.shift_paste = false
        // this.spinner.hide()
      }
      else {
        this.addWithConflict(res, data, 'paste')
      }
    }, (error => {
      console.log(error);

    }))
  }

  handleRosterResponse(res, status, value): void {
    if (res.success) {
      this.toast.toastNotification(res.message, status);
      this.runsheet_roster.sendFilterData();
      this.close();
      this.global.timestamp = Date.now();
    } else {
      this.addWithConflict(res, value, 'saveRoster');
    }

    this.global.timestamp = Date.now();
    // this.spinner.hide();
  }

  close(data?) {
    this.modalService.dismissAll(data);
    this.edit_shift_side = !this.edit_shift_side;
  }

  trackByJobRosterId(index: number, job: any): number {
    return job.roster_id;
  }

  checkWeek(lockWeek) {
    return this.commonService.checkWeek(lockWeek)
  }

  public executeFunction(job) {
    if (this.isContextMenuDisabled) {
      this.shiftSelect(job.roster_id);
    } else {
      this.showEditOption(job);
    }
  }

  shiftSelect(id) {
    this.shift_select = true
    const index = this.selectedCardIds.indexOf(id);
    if (index > -1) {
      this.selectedCardIds.splice(index, 1);
      if (this.selectedCardIds.length == 0) {
        this.isContextMenuDisabled = false;
      }
    } else {
      this.selectedCardIds.push(id);
      this.isContextMenuDisabled = true;
    }
  }

  showEditOption(job: any) {
    this.job_id_match = job.roster_id;
    this.edit_shift_side = !this.edit_shift_side;
  }

  getFormattedName(job: any): any {
    return this.commonService.getFormattedName(job);
  }

  editShiftAction(type, job, site?, day?) {
    this.shiftEdited = true
    this.clickedboxDate1 = moment(day, "ddd , DD/MM").format('ddd , DD MMM')

    this.currentDay = day
    if (type == 'edit') {
      let updateTime = this.modalService.open(UpdateTimeComponent, { animation: true, centered: true });
      let start = job.start;
      let end = job.end;
      updateTime.componentInstance.start = JSON.stringify(start);
      updateTime.componentInstance.end = JSON.stringify(end);
      updateTime.componentInstance.currentDay = day.momentFormat;
      updateTime.componentInstance.runsheet_roster = 'run_sheet';
      updateTime.componentInstance.rosterPermissions = day.rosterPermissions;
      updateTime.result.then(
        (result) => {
          result.id = job.roster_id,
            result.admin_id = this.global.admin.admin_id
          this.roster.updateTime(result).subscribe(res => {
            let status = 'Runsheet Job Roster Operation!'
            if (res.success) {
              this.toast.toastNotification(res.message, status)
              this.runsheet_roster.sendFilterData()
            }
          })
        });
    }
    else if (type == 'popover') {
      const modalRef = this.modalService.open(RunsheetAdvanceShiftComponent, {
        size: 'lg',
        centered: true,
        backdropClass: 'modal-no-backdrop',
        backdrop: 'static',
        windowClass: 'modelZ-index',
        animation: true,
      });
      modalRef.componentInstance.calenderType = this.calenderType;
      modalRef.componentInstance.site = { site_name: site.run_sheet_title, id: site.run_sheet_id, customer_id: site?.customer_id };
      modalRef.componentInstance.currentDay = this.currentDay;
      modalRef.componentInstance.job = job.roster_id;
      modalRef.result.then(
        (result) => {
          if (result == true) {
            this.runsheet_roster.sendFilterData()
          }
        },
        (reason) => {
          console.log(`Dismissed with reason: ${this.getDismissReason(reason)}`);
        }
      );
    }
    else if (type == 'delete') {
      if (job.roster_id) {
        this.commonService.RunsheetshiftDelReason(job.roster_id).subscribe(resp => {
          if (resp.clickedButtonID === 'submit') {
            this.sites.forEach(site => {
              site.jobRoster.forEach(rosterItem => {
                if (rosterItem.roster_id === job.roster_id) {
                  const indexToRemove = site.jobRoster.indexOf(rosterItem);
                  if (indexToRemove !== -1) {
                    site.jobRoster.splice(indexToRemove, 1);
                    this.changeDetect.markForCheck();
                  } else {
                    console.log("Index not found");
                  }
                }
              });
            });
          }
        });
      }
    }
  }

  loadPhone(phone) {
    this.showMobile = !this.showMobile;
    // Check if the AircallPhone instance already exists
    if (!this.aircallPhone) {
      this.aircallPhone = new AircallPhone({
        domToLoadPhone: '#phone',
        onLogin: (settings) => {
          this.aircallPhone.isLoggedIn(response => {
            if (response) {
              console.log('User is logged in');
              this.dialPhoneNumber(phone);
            } else {
              this.toast.toastNotification1('User is not logged in', 'Call Operation!')
            }
          });
        },
        onLogout: () => {
          this.toast.toastNotification('User is logged out', 'Call Operation!')
        },
      });
    } else {
      this.dialPhoneNumber(phone);
    }
  }

  dialPhoneNumber(phoneNumber: string) {
    if (this.aircallPhone) {
      this.aircallPhone.send('dial_number', { phone_number: phoneNumber }, (success, data) => {
        console.log(success, data);
      });
    } else {
      console.error('AircallPhone instance is not available.');
    }
  }

  job
  sendMessage(job) {
    this.job = job
    this.modalRef = this.modalService.open(this.sendMsg, {
    });
  }

  sendMesg() {
    if (!this.job.phone) {
      this.toast.toastNotification1('This staff has not entered their number', 'Request Incomplete!');
    } else {
      const recipientIds = [parseInt(this.job.guard_id, 10)];
      this.commonService.sendAndConfirmMessage(this.sdMessage.value.msg, recipientIds).subscribe((response) => {
        if (response.success) {
          this.modalRef.close();
          this.sdMessage.reset();
          this.toast.toastNotification(response.message, 'Message Operation!');
        } else {
          this.toast.toastNotification('Something went wrong', 'Message Operation!');
        }
      });
    }
  }

  deletedata(id) {
    this.siteid = id;
    let data = {
      id: this.siteid,
      reason: this.AddDeleteReason.value.reason,
      admin_id: this.global.admin.admin_id
    }
    this.patrolling.deleteRunsheet(data).subscribe(({ message, success }) => {
      if (success) {
        this.toast.toastNotification1(message, 'Runsheet Operation!');
        this.runsheet_roster.sendFilterData()
      }
      else {
        this.dialog.open(SiteShiftsComponent, {
          width: '500px',
          data: {
            message: message
          }
        }).afterClosed().subscribe(response => {
          if (response === 'yes') {
            let data = {
              id: this.siteid,
              reason: this.AddDeleteReason.value.reason,
              is_confirm: 'yes',
              admin_id: this.global.admin.admin_id
            }
            this.patrolling.deleteRunsheet(data).subscribe(({ message, success }) => {
              if (success) {
                this.toast.toastNotification1(message, 'Runsheet Operation!')
                this.runsheet_roster.sendFilterData()
              }
            });
          }
        });
      }
    },
      (error) => {
        this.toast.toastNotification1('Something went wrong. Please contact with support team.', 'Runsheet Operation!');
      });
    this.modalRef.close();
  }

  totalHours
  shiftDayhours
  @Input() set days_hours(days_hours) {
    if (days_hours) {
      let dayHours = days_hours.days_hours
      this.totalHours = days_hours.total_hours
      this.shiftDayhours = Object.entries(dayHours).map(([key, value]) => ({ date: key, count: value }));
    }
  }
}
