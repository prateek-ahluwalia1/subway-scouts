import { ChangeDetectionStrategy, ChangeDetectorRef, Component, Inject, Input, OnDestroy, OnInit, TemplateRef, ViewChild } from "@angular/core";
import { GlobalVariable } from "app/shared/global";
import moment from "moment";
import { faTrash, faCreditCard, faPencil, faPhone, faMessage } from "@fortawesome/free-solid-svg-icons";
import { ModalDismissReasons, NgbModal, NgbModalRef, NgbPopover } from "@ng-bootstrap/ng-bootstrap";
import { FormBuilder, FormControl, FormGroup, Validators } from "@angular/forms";
import { MatDialog } from "@angular/material/dialog"; 
import { CdkDragDrop } from "@angular/cdk/drag-drop";
import { JobRoster1Service } from "app/services/job-roster1.service";
import { ToastServiceService } from "app/services/toast-service.service";
import { JobRosterComponent } from "../job-roster.component";
import { MatMenuTrigger } from "@angular/material/menu";
import { AppearanceAnimation, ButtonLayoutDisplay, ButtonMaker, ConfirmBoxInitializer, DialogBelonging, DialogLayoutDisplay, DisappearanceAnimation } from "@costlydeveloper/ngx-awesome-popup";
import { SiteService } from "app/services/site.service";
import { FormServiceOneTwoService } from "app/services/form-service-one-two.service";
import { TrackAdminActivityService } from "app/services/track-admin-activity.service";
import { NgxSpinnerService } from "ngx-spinner";
import AircallPhone from "aircall-everywhere";
import { SmsServicesService } from "app/services/sms-services.service";
import { PermissionsService } from "app/services/permissions.service";
import { ActivatedRoute } from "@angular/router";
import { Subject, filter, takeUntil } from "rxjs";
import { AdvanceShiftComponent } from "../advance-shift/advance-shift.component";
import { CommonOneTwoService } from "../common-one-two.service";
import { SplitComponent } from "../split/split.component";
import { JobshiftActivityComponent } from "app/modules/admin/models/jobshift-activity/jobshift-activity.component";
import { StaffActivityDetailsComponent } from "app/modules/admin/models/staff-activity-details/staff-activity-details.component";
import { ProfileCommentComponent } from "app/modules/admin/profile-comment/profile-comment.component";
import { SiteShiftsComponent } from "app/modules/admin/site-shifts/site-shifts.component";
import { CreateSiteComponent } from "app/modules/admin/models/create-site/create-site.component";
import { UpdateTimeComponent } from "app/modules/admin/models/update-time/update-time.component";
import { AddCustomTemplateComponent } from "app/modules/admin/models/add-custom-template/add-custom-template.component";
import { CopyDaywiseShiftsComponent } from "../copy-daywise-shifts/copy-daywise-shifts.component";

@Component({
  selector: "app-one-weeks",
  templateUrl: "./one-weeks.component.html",
  styleUrls: ["./one-weeks.component.scss"],
  providers: [MatMenuTrigger],
  changeDetection: ChangeDetectionStrategy.OnPush,
})

export class OneWeeksComponent implements OnInit, OnDestroy {

  job_id_match;
  site_id_match;
  edit_shift_side = false;
  popover = false;
  clickedboxDate1;
  faPencil = faPencil;
  faCreditCard = faCreditCard;
  faTrash = faTrash;
  phone = faPhone;
  msg = faMessage;
  // shift_paste: boolean = false;
  shift_select: boolean = false;
  copyID;
  selectIds;
  @Input("calenderType") calenderType;
  @Input("rosterStatus") rosterStatus;
  signInOut = [];
  shiftDayhours;
  totalHours;
  private _data: any[];
  job_new_roster = [];
  sites = [];
  buit_in_template = [];
  searchSites;
  guard_id;
  roster_id;
  shiftEdited;
  loginUser;
  @ViewChild("deletecontent") deletecontent: TemplateRef<any>;
  @ViewChild("unassignedOperationNotes")
  unassignedOperationNotes: TemplateRef<any>;
  private aircallPhone: AircallPhone;
  showMobile: boolean = false;
  rosterPermissions: any;
  private _unsubscribeAll: Subject<any> = new Subject<any>();
  originalArray: any[] = [];
  currentDay: any;
  document_type = {
    0: "security_license",
    1: "visa"
  }
  @ViewChild('splitSidenav') splitSidenav: SplitComponent;
  jobRosterId: string;
  showButton = true;
  guardsIds;
  fromMultiGuard;
  guardIndex;
  customersIds;
  fromMultiCustomer;
  closeResult = "";
  drop_data;
  copyJob: any;
  selectJob: any;
  //site action for delete/select/delete
  selectedSitesIds: number[] = [];
  selectedGuardsIds: number[] = [];
  siteid;
  modalRef;
  AddDeleteReason: FormGroup;
  sdMessage: FormGroup;
  OperationNotes: FormGroup;
  isContextMenuDisabled: boolean = false;
  selectedCardIds: number[] = [];
  @ViewChild("sendOtp") sendOtp: TemplateRef<any>;
  modalMessage :string = ''
  otpModalmessage: string = ''
  otpValue: string = '';
  private pendingValue: any = null;
  private pendingType: string | undefined = undefined;

  constructor( private modalService: NgbModal, public dialog: MatDialog, private rosterService: JobRoster1Service,
    private toast: ToastServiceService, public jobroster: JobRosterComponent, private fb: FormBuilder, public global: GlobalVariable,
    @Inject("dialogBelonging") public dialogBelonging: DialogBelonging, private siteService: SiteService, private trackAdmin: TrackAdminActivityService,
    public smsService: SmsServicesService, private spinner: NgxSpinnerService, private permissionService: PermissionsService,
    private route: ActivatedRoute, private changeDetect: ChangeDetectorRef, private commonService: CommonOneTwoService,
    private jobRoster: JobRoster1Service, private globals: GlobalVariable ) 
  {
    this.global.getWeekDays = [];
    var currentDate = moment();
    var weekStart = currentDate.clone().startOf("isoWeek");
    this.global.start = weekStart.format("MM-DD-YYYY");
    var weekEnd = currentDate.clone().endOf("isoWeek");
    this.global.end = weekEnd.format("MM-DD-YYYY");
    this.global.selectedDate = `${weekStart.format("MMM D")} - ${weekEnd.format("D, YYYY")}`;
    for (var i = 0; i <= 6; i++) {
      const customFormat = moment(weekStart).add(i, "days").format("ddd , DD/MM");
      const momentFormat = moment(weekStart).add(i, "days").format("MM-DD-YYYY");
      this.global.getWeekDays.push({ customFormat, momentFormat });
    }
  }

  ngOnInit(): void {
    // console.log("Roster status", this.rosterStatus)
    const per = this.permissionService.getPermissionsByTitle("WFM Tools");
    this.rosterPermissions = per?.childPage?.find((item) => item.title === "Staff Roster/Scheduling");
    this.sites = [];
    this.rosterService.sites$.pipe(filter((res) => res && res.data),takeUntil(this._unsubscribeAll))
    .subscribe((sites: any) => {
      if (this._data.length > 0) {
        this.sites = sites?.data.filter((item) =>
          this._data.includes(item.id)
        );
      } else if (sites.data.length > 0) {
        this.sites = sites.data;
        this.originalArray = sites.data;
      } else {
        this.sites = sites.data;
      }
      this.sites.sort((a, b) => a.site_name.localeCompare(b.site_name));
      this.changeDetect.markForCheck();
    });

    this.rosterService.job$.pipe(filter((res) => res && res.data), takeUntil(this._unsubscribeAll))
    .subscribe((res: any) => {
      if (res.data && res.data.length > 0) {
        res.data.forEach((mainArray) => {
          if (mainArray.jobRoster.length > 0) {
            mainArray.jobRoster.forEach((object) => {
              this.sites?.forEach((oldArray) => {
                if (oldArray.id == object.site_id) {
                  const index = oldArray.jobRoster.findIndex(
                    (item) => item.roster_id == object.roster_id
                  );
                  if (index != -1) {
                    oldArray.jobRoster.splice(index, 1);
                    oldArray.jobRoster.push(object);
                    this.global.timestamp = Date.now();
                  } 
                  else {
                    oldArray.jobRoster.push(object);
                    if (!this.jobroster.shift_ids.includes(object.roster_id)) {
                      this.jobroster.shift_ids.push(object.roster_id);
                    }
                    this.global.timestamp = Date.now();
                  }
                }
              });
            });
          }
        });
      }
      this.changeDetect.markForCheck();
    });

    this.route.queryParamMap.subscribe((params) => {
      this.route.paramMap.subscribe((params1) => {
        const id = params1.get("id");
        const guardId = params.get("staff_id");
        const shiftId = params.get("shift_id");
        let job = {
          job_id: shiftId,
          guard_id: guardId,
        };
        if (id && guardId) {
          this.staffDetails("activity", job);
        }
      });
    });

    this.AddDeleteReason = this.fb.group({
      reason: new FormControl("", Validators.required),
    });

    this.sdMessage = this.fb.group({
      msg: new FormControl("", Validators.required),
    });

    this.OperationNotes = this.fb.group({
      unassignedoperationNotes: new FormControl(""),
    });

    this.loginUser = this.global.admin.admin_user_type;

    this.changeDetect.markForCheck();

    this.route.paramMap.subscribe(params => {
      this.jobRosterId = params.get('id');
      // console.log('Job ID:', this.jobRosterId);
    });
  }

  ngOnDestroy(): void {
    this.rosterService.clearSitesData();
    this._unsubscribeAll.next(null);
    this._unsubscribeAll.complete();
  }

  receiveDataSingleSelection(data: string) {
    this.fromMultiGuard = data;
    this.guardsIds = this.fromMultiGuard.value.map((item) => item.id);
  }

  receiveDataFromChildGuardsSingle(data: string) {
    this.guardIndex = data;
  }

  receiveDataFromChild(data: string) {
    this.fromMultiCustomer = data;
    this.customersIds = this.fromMultiCustomer.value.map((item) => item.id);
  }

  getTemplate() {
    this.rosterService.getTemplate().subscribe((res) => {
      if (res.success) {
        res.data.forEach((element) => {
          const start = moment(element.start, "DD-MM-YYYY HH:mm");
          const end = moment(element.end, "DD-MM-YYYY HH:mm");
          element.start = start.format("HH:mm");
          element.end = end.format("HH:mm");
        });
        this.buit_in_template = res.data;
      }
    });
  }

  /*Show the add Custom Template popOver*/
  showShiftTemplate(day: any, resource_id: any) {
    this.job_id_match = day;
    this.site_id_match = resource_id;
    this.edit_shift_side = !this.edit_shift_side;
    this.getTemplate();
  }

  /*Closing the open modals and popover and sidebar */
  close(data?) {
    this.modalService.dismissAll(data);
    this.edit_shift_side = !this.edit_shift_side; //It closes successfully
  }

  /*open modal to add custom template*/
  customTemplateModal(day, p: NgbPopover) {
    p.close();
    this.close();
    const modalRef = this.modalService.open(AddCustomTemplateComponent, {
      windowClass: "custom-class",
      animation: true,
    });
    this.popover = true;
    modalRef.componentInstance.fromParent = day;
    modalRef.result.then(
      (result) => {
        console.log("Modal Result", `Closed with: ${result}`);
        if (result == "addTemplate") {
        }
      },
      (reason) => {
        console.log(
          "Modal Closed Reason -> ",
          `Dismissed ${this.getDismissReason(reason)}`
        );
      }
    );
  }

  private getDismissReason(reason: any): string {
    if (reason === ModalDismissReasons.ESC) {
      return "by pressing ESC";
    } else if (reason === ModalDismissReasons.BACKDROP_CLICK) {
      return "by clicking on a backdrop";
    } else {
      return `with: ${reason}`;
    }
  }

  saveRoster(value): void {
    this.spinner.show();
    const isAdmin = this.global.admin.admin_id;
    const rosterId = localStorage.getItem("rosterId");
    value.admin = isAdmin;
    value.roster_id = rosterId;
    this.rosterService["addShift"](value).subscribe(
      (res) => {
        const status = "Job Roster Operation";
        this.handleRosterResponse(res, status, value);
      },
      (error) => {
        this.spinner.hide();
        this.toast.toastNotification1(
          "Something went wrong. Please contact the support team.",
          "Request Incomplete"
        );
      }
    );
  }

  handleRosterResponse(res, status, value): void {
    if (res.success) {
      this.toast.toastNotification(res.message, status);
      this.jobroster.sendFilterData();
      this.close();
      this.global.timestamp = Date.now();
    } else {
      this.addWithConflict(res, value, "saveRoster");
    }
    this.global.timestamp = Date.now();
    this.spinner.hide();
  }

  /*Add Custom Shift to array*/
  addCustomShift(custom_template: any, day: any, site_id: any) {
    let start = custom_template.start;
    let end = custom_template.end;
    let object = this.global.AddTimeDate(start, end, day.momentFormat);
    let value = {
      start: object.start,
      end: object.end,
      site_id: site_id,
      shift_type: "template_rost",
    };
    this.saveRoster(value); // Call the saveRoster function
  }

  showEditOption(job: any) {
    this.job_id_match = job.roster_id;
    this.edit_shift_side = !this.edit_shift_side;
  }

  editShiftAction(type, job, site?, day?) {
    this.shiftEdited = true;
    this.clickedboxDate1 = moment(day, "ddd , DD/MM").format("ddd , DD MMM");
    this.currentDay = day;
    if (type == "edit") {
      let updateTime = this.modalService.open(UpdateTimeComponent, {
        animation: true,
        centered: true,
      });
      let start = job.start;
      let end = job.end;
      updateTime.componentInstance.start = JSON.stringify(start);
      updateTime.componentInstance.end = JSON.stringify(end);
      updateTime.componentInstance.currentDay = day.momentFormat;
      updateTime.componentInstance.rosterPermissions = day.rosterPermissions;
      updateTime.result.then((result) => {
        // if (result) {
          (result.id = job.roster_id),
          (result.admin_id = this.global.admin.admin_id);
          this.rosterService.updateTime(result).subscribe((res) => {
            let status = "Job Roster Operation!";
            if (res.success) {
              this.toast.toastNotification(res.message, status);
              this.jobroster.sendFilterData();
            }
          });
        // }
      });
    } else if (type == "popover") {
      const modalRef = this.modalService.open(AdvanceShiftComponent, {
        size: "lg",
        centered: true,
        backdropClass: "modal-no-backdrop",
        backdrop: "static",
        windowClass: "modelZ-index",
        animation: true,
      });
      modalRef.componentInstance.calenderType = this.calenderType;
      modalRef.componentInstance.site = {
        site_name: site.site_name,
        id: site.id,
        customer_id: site?.customer_id,
      };
      modalRef.componentInstance.currentDay = this.currentDay;
      modalRef.componentInstance.job = job.roster_id;
      modalRef.componentInstance.accessJobRoaster = this.jobRosterId;
      modalRef.componentInstance.permissions = this.rosterPermissions;
      modalRef.result.then(
        (result) => {
          if (result == true) {
            this.jobroster.sendFilterData();
          }
        },
        (reason) => {
          console.log(
            `Dismissed with reason: ${this.getDismissReason(reason)}`
          );
        }
      );
    } else if (type == "delete") {
      if (job.roster_id) {
        this.commonService.shiftDelReason(job.roster_id).subscribe((resp) => {
          if (resp.clickedButtonID === "submit") {
            this.jobroster.sendFilterData();
            // this.sites.forEach(site => {
            //   site.jobRoster.forEach(rosterItem => {
            //     if (rosterItem.roster_id === job.roster_id) {
            //       this.jobroster.shift_ids = this.jobroster.shift_ids.filter(id => id !== job.roster_id);
            //       console.log(this.jobroster.shift_ids);
            //       const indexToRemove = site.jobRoster.indexOf(rosterItem);
            //       if (indexToRemove !== -1) {
            //         site.jobRoster.splice(indexToRemove, 1);
            //         this.changeDetect.markForCheck();
            //       } else {
            //         console.log("Index not found");
            //       }
            //     }
            //   });
            // });
          }
        });
      }
    }
  }

  drop(event: CdkDragDrop<string[]>) {
    if (this.rosterStatus == "active" && this.loginUser != "customer") {
      this.openConfirmBox("drop", event);
      event.item.data.date = event.container.id;
      this.job_new_roster.push(event.item.data);
    } else {
      const newConfirmBox = new ConfirmBoxInitializer();
      newConfirmBox.setTitle("Access Denied");
      newConfirmBox.setMessage("This roster have not access to copy or drop");
      // Choose layout color type
      newConfirmBox.setConfig({
        layoutType: DialogLayoutDisplay.DANGER, // SUCCESS | INFO | NONE | DANGER | WARNING
        animationIn: AppearanceAnimation.ZOOM_IN_ROTATE, // BOUNCE_IN | SWING | ZOOM_IN | ZOOM_IN_ROTATE | ELASTIC | JELLO | FADE_IN | SLIDE_IN_UP | SLIDE_IN_DOWN | SLIDE_IN_LEFT | SLIDE_IN_RIGHT | NONE
        animationOut: DisappearanceAnimation.BOUNCE_OUT, // BOUNCE_OUT | ZOOM_OUT | ZOOM_OUT_WIND | ZOOM_OUT_ROTATE | FLIP_OUT | SLIDE_OUT_UP | SLIDE_OUT_DOWN | SLIDE_OUT_LEFT | SLIDE_OUT_RIGHT | NONE
        allowHtmlMessage: true,
        buttonPosition: "center", // optional
      });
      newConfirmBox.setButtons([
        new ButtonMaker("Discard", "Discard", ButtonLayoutDisplay.INFO),
      ]);
      // Simply open the popup and observe button click
      newConfirmBox.openConfirmBox$().subscribe((resp) => {
        if (resp.clickedButtonID == "Discard") {
          this.jobroster.sendFilterData();
        }
      });
    }
  }

  openConfirmBox(id, event?) {
    const newConfirmBox = new ConfirmBoxInitializer();
    if (id == "drop") {
      newConfirmBox.setTitle("Copy & Drop");
      newConfirmBox.setMessage("What do you want to do?");
    } else {
      newConfirmBox.setTitle("Confirm Action");
      newConfirmBox.setMessage("Are you sure you want to confirm this action?");
    }
    // Choose layout color type
    newConfirmBox.setConfig({
      layoutType: DialogLayoutDisplay.CUSTOM_TWO, // SUCCESS | INFO | NONE | DANGER | WARNING
      animationIn: AppearanceAnimation.FADE_IN, // BOUNCE_IN | SWING | ZOOM_IN | ZOOM_IN_ROTATE | ELASTIC | JELLO | FADE_IN | SLIDE_IN_UP | SLIDE_IN_DOWN | SLIDE_IN_LEFT | SLIDE_IN_RIGHT | NONE
      animationOut: DisappearanceAnimation.ZOOM_OUT_ROTATE, // BOUNCE_OUT | ZOOM_OUT | ZOOM_OUT_WIND | ZOOM_OUT_ROTATE | FLIP_OUT | SLIDE_OUT_UP | SLIDE_OUT_DOWN | SLIDE_OUT_LEFT | SLIDE_OUT_RIGHT | NONE
      allowHtmlMessage: true,
      buttonPosition: "center", // optional
    });

    if (id == "drop") {
      newConfirmBox.setButtons([
        new ButtonMaker("Copy", "copy", ButtonLayoutDisplay.SUCCESS),
        new ButtonMaker("Drop", "drop", ButtonLayoutDisplay.INFO),
        new ButtonMaker("Discard", "Discard", ButtonLayoutDisplay.DANGER),
      ]);
    } else {
      newConfirmBox.setButtons([
        new ButtonMaker("Confirm", "Confirm", ButtonLayoutDisplay.SUCCESS),
        new ButtonMaker("Discard", "Discard", ButtonLayoutDisplay.INFO),
      ]);
    }

    // Simply open the popup and observe button click
    newConfirmBox.openConfirmBox$().subscribe((resp) => {
      if (resp.clickedButtonID == "copy") {
        event.item.data.admin_id = this.global.admin.admin_id;
        this.dragDropData(event);
      } else if (resp.clickedButtonID == "drop") {
        event.item.data.type = "drop";
        event.item.data.admin_id = this.global.admin.admin_id;
        this.dragDropData(event);
      } else if (resp.clickedButtonID == "Discard") {
        this.jobroster.sendFilterData();
      }
    });
  }

  dragDropData(event) {
    event.admin_id = this.global.admin.admin_id;
    let object = this.global.AddTimeDate(
      event.item.data.start_time,
      event.item.data.end_time,
      event.container.id
    );
    event.item.data.start = object.start;
    event.item.data.end = object.end;
    this.dragDropApi(event.item.data);
  }

  dragDropApi(data) {
    this.spinner.show();
    this.rosterService.dragDrop(data).subscribe(
      (res) => {
        let status = "Job Roster Operation!";
        if (res.success) {
          this.toast.toastNotification(res.message, status);
          this.jobroster.sendFilterData();
          this.global.timestamp = Date.now();
          if (data.type == "drop") {
            // console.log("drop with out conflict");
            this.trackAdmin.storeActivity("Job Roster", `Drop shift  ${data.first_name + " " + data.last_name} to ${data.start}`, this.jobroster.routeId).subscribe((res) => { });
          } else {
            this.trackAdmin.storeActivity("Job Roster", `Copy shift  ${data.first_name + " " + data.last_name} to ${data.start}`, this.jobroster.routeId).subscribe((res) => { });
          }
          this.spinner.hide();
        } else {
          this.addWithConflict(res, data, "copy");
          this.global.timestamp = Date.now();
        }
      },
      (error) => { }
    );
  }

  /////shift right click copy option
  shiftCopy(job: any) {
    if (!this.shift_select) {
      this.shift_select = false;
      this.copyID = null;
      this.copyJob = null;
      this.global.shift_paste = false;
    } else {
      this.global.shift_paste = true;
      this.copyID = job.roster_id;
      this.copyJob = job;
      if (!this.selectedCardIds.includes(job.roster_id)) {
        this.selectedCardIds.push(job.roster_id);
      }
      this.isContextMenuDisabled = false;
      this.global.isContextMenuDisabled = this.isContextMenuDisabled;
    }
  }

  paste(day, id) {
    let data = this.commonService.paste(
      day,
      id,
      this.copyJob.roster_id,
      this.calenderType,
      this.copyJob
    );
    this.savePaste(data);
  }

  shiftSelect(data) {
    this.shift_select = true;
    const index = this.selectedCardIds.indexOf(data.roster_id);
    if (index > -1) {
      this.selectedCardIds.splice(index, 1);
      if (this.copyID === data.roster_id) {
        this.copyID = null;
        this.copyJob = null;
        this.global.shift_paste = false;
      }
      if (this.selectedCardIds.length == 0) {
        this.isContextMenuDisabled = false;
        this.global.isContextMenuDisabled = this.isContextMenuDisabled;
      }
    } 
    else {
      this.selectedCardIds.push(data.roster_id);
      this.isContextMenuDisabled = false;
      this.global.isContextMenuDisabled = this.isContextMenuDisabled;
      this.shiftCopy(data)
    }
    this.global.selectedCardIds = this.selectedCardIds;
    this.isContextMenuDisabled = this.selectedCardIds.length > 0;
    this.global.isContextMenuDisabled = this.isContextMenuDisabled;
  }

  shouldDisableContextMenu(job: any): boolean {
    return this.selectedCardIds.includes(job.roster_id) || this.rosterStatus !== 'active';
  }

  //execute two function
  public executeFunction(job) {
    // if (this.isContextMenuDisabled) {
    //   this.shiftSelect(job);
    // } else {
      this.showEditOption(job);
    // }
  }

  /////save paste roster
  savePaste(data) {
    this.spinner.show();
    this.rosterService.copyShift(data).subscribe(
      (res) => {
        let status = "Job Roster Operation!";
        if (res.success) {
          this.toast.toastNotification(res.message, status);
          this.jobroster.sendFilterData();
          this.global.timestamp = Date.now();
          // this.shift_paste = false;
          // this.copyID = null;
          this.trackAdmin.storeActivity("Job Roster", `Copy shift of ${this.copyJob.first_name + " " + this.copyJob.last_name } start: ${this.copyJob.start + " end:" + this.copyJob.end} in ${data.newStart }`, this.jobroster.routeId).subscribe((res) => { });
          this.spinner.hide();
        } else {
          this.addWithConflict(res, data, "paste");
        }
      },
      (error) => {
        console.log(error);
      }
    );
  }

  // confirm conflict shift for add
  addWithConflict(res, value, type?) {
    const newConfirmBox = new ConfirmBoxInitializer();
    let layoutType;
    layoutType =
      res.msg !== "Sorry Staff On Leave!"
        ? DialogLayoutDisplay.SUCCESS
        : DialogLayoutDisplay.DANGER;

    let buttons: ButtonMaker[] = [];

    if (res.hide) {
      newConfirmBox.setTitle("Access Denied!");
      newConfirmBox.setMessage(res.message);
      layoutType = DialogLayoutDisplay.DANGER;
      buttons.push(
        new ButtonMaker("Discard", "Discard", ButtonLayoutDisplay.DANGER)
      );
    } 
    else if(res.require_otp){
      this.spinner.hide();
      this.showOtpVerificationModal(res, value, type);
      return;
    }
    else {
      newConfirmBox.setTitle("Confirm Action");
      newConfirmBox.setMessage(res.message);
      // buttons = [
      //   new ButtonMaker('Confirm', 'Confirm', ButtonLayoutDisplay.SUCCESS),
      //   new ButtonMaker('Discard', 'Discard', ButtonLayoutDisplay.DANGER)
      // ];
      if (
        res.message !==
        "Sign-in shift does not drop to the next or previous date!"
      ) {
        buttons = [
          new ButtonMaker("Confirm", "Confirm", ButtonLayoutDisplay.SUCCESS),
          new ButtonMaker("Discard", "Discard", ButtonLayoutDisplay.DANGER),
        ];
      } else {
        buttons = [
          new ButtonMaker("Discard", "Discard", ButtonLayoutDisplay.DANGER),
        ];
      }
    }

    newConfirmBox.setConfig({
      layoutType,
      animationIn: AppearanceAnimation.ZOOM_IN_ROTATE,
      animationOut: DisappearanceAnimation.ZOOM_OUT_WIND,
      allowHtmlMessage: true,
      buttonPosition: "center",
    });
    newConfirmBox.setButtons(buttons);
    newConfirmBox.openConfirmBox$().subscribe((resp) => {
      if (resp.clickedButtonID == "Confirm") {
        if (type === "saveRoster" || type === "paste" || type === "copy") {
          value.shift_confirm = "yes";
          if (type === "saveRoster") this.saveRoster(value);
          else if (type === "paste") this.savePaste(value);
          else if (type === "copy") this.dragDropApi(value);
        }
      } else {
        this.spinner.hide();
        this.jobroster.sendFilterData();
      }
    });
  }

  private showOtpVerificationModal(res: any, value: any, type?: string) {
    this.otpModalmessage = res.message;
    this.otpValue = '';
    this.pendingValue = value;
    this.pendingType = type;
    this.modalRef = this.modalService.open(this.sendOtp, {
      centered: true,              
      size: '350px',                   
      backdrop: 'static',          
    });
    this.modalRef.result.then(
      (result) => {
        this.resetPendingData();
      },
      (reason) => {
        this.resetPendingData();
        this.spinner.hide();
      }
    );
  }

  private resetPendingData() {
    this.pendingValue = null;
    this.pendingType = undefined;
  }

  onSendOtp() {
    const otp = this.otpValue?.trim();
    if (!otp) {
      this.toast.toastNotification1('Otp is required', 'Otp Verification');
      return;
    }

    if (!this.pendingValue) {
      console.log('No pending shift data found');
      this.modalRef?.close();
      return;
    }

    this.pendingValue.otp = otp;
    this.pendingValue.shift_confirm = 'yes';

    if (this.pendingType === 'paste') {
      this.savePaste(this.pendingValue);
    }

    if (this.pendingType === 'copy') {
      this.dragDropApi(this.pendingValue);
    }

    this.modalRef?.close();
    this.resetPendingData();
    this.jobroster.sendFilterData();
  }

  staffDetail(type, job) {
    this.isContextMenuDisabled = !this.isContextMenuDisabled;
    this.global.isContextMenuDisabled = this.isContextMenuDisabled;
    this.staffDetails(type, job);
  }

  siteAction(type, id) {
    if (type == "edit") {
      if (this.calenderType != "staff") {
        const modalRef = this.modalService.open(CreateSiteComponent, {
          size: "xl",
        });
        modalRef.componentInstance.fromParent = id;
        modalRef.result.then(
          (result) => {
            console.log("Modal Result", `Closed with: ${result}`);
          },
          (reason) => {
            let rea = this.getDismissReason(reason);
            if (rea == "update") {
              this.jobroster.sendFilterData();
            }
          }
        );
      }
    } else if (type == "delete") {
      this.siteid = id;
      this.modalRef = this.modalService.open(this.deletecontent);
    } 
    else {
      console.log("Id", id)
      const index = this.selectedSitesIds.indexOf(id);
      if (index > -1) {
        this.selectedSitesIds.splice(index, 1);
        if (this.selectedSitesIds.length == 0) {
        }
      } else {
        this.selectedSitesIds.push(id);
        console.log("Selected site", this.selectedSitesIds)
      }
      this.global.selectedSitesIds = this.selectedSitesIds;
    }
  }

  deletedata(id) {
    this.siteid = id;
    let data = {
      id: this.siteid,
      reason: this.AddDeleteReason.value.reason,
      admin_id: this.global.admin.admin_id,
    };
    this.siteService.delSite(data).subscribe(
      ({ message, success }) => {
        if (success) {
          this.toast.toastNotification1(message, "Site Operation!");
          this.jobroster.sendFilterData();
        } else {
          this.dialog
            .open(SiteShiftsComponent, {
              width: "500px",
              data: {
                message: message,
              },
            })
            .afterClosed()
            .subscribe((response) => {
              if (response === "yes") {
                let data = {
                  id: this.siteid,
                  reason: this.AddDeleteReason.value.reason,
                  is_confirm: "yes",
                  admin_id: this.global.admin.admin_id,
                };
                this.siteService
                  .delSite(data)
                  .subscribe(({ message, success }) => {
                    if (success) {
                      this.toast.toastNotification1(message, "Site Operation!");
                      this.jobroster.sendFilterData();
                    }
                  });
              }
            });
          // this.toast.toastNotification1(message, 'Site Operation!');
        }
      },
      (error) => {
        this.toast.toastNotification1(
          "Something went wrong. Please contact with support team.",
          "Site Operation!"
        );
      }
    );
    this.modalRef.close();
  }

  getFormattedName(job: any): any {
    return this.commonService.getFormattedName(job);
  }

  // call to staff
  loadPhone(phone) {
    this.showMobile = !this.showMobile;
    // Check if the AircallPhone instance already exists
    if (!this.aircallPhone) {
      this.aircallPhone = new AircallPhone({
        domToLoadPhone: "#phone",
        onLogin: (settings) => {
          this.aircallPhone.isLoggedIn((response) => {
            if (response) {
              console.log("User is logged in");
              this.dialPhoneNumber(phone);
            } else {
              this.toast.toastNotification1(
                "User is not logged in",
                "Call Operation!"
              );
            }
          });
        },
        onLogout: () => {
          this.toast.toastNotification("User is logged out", "Call Operation!");
        },
      });
    } else {
      this.dialPhoneNumber(phone);
    }
  }

  dialPhoneNumber(phoneNumber: string) {
    if (this.aircallPhone) {
      this.aircallPhone.send(
        "dial_number",
        { phone_number: phoneNumber },
        (success, data) => {
          console.log(success, data);
        }
      );
    } else {
      console.error("AircallPhone instance is not available.");
    }
  }

  shideMobile() {
    this.showMobile = !this.showMobile;
  }

  job;

  sendMesg() {
    if (!this.job.phone) {
      this.toast.toastNotification1(
        "This staff has not entered their number",
        "Request Incomplete!"
      );
    } else {
      const recipientIds = [parseInt(this.job.guard_id, 10)];
      this.commonService
        .sendAndConfirmMessage(this.sdMessage.value.msg, recipientIds)
        .subscribe((response) => {
          if (response.success) {
            this.modalRef.close();
            this.sdMessage.reset();
            this.toast.toastNotification(
              response.message,
              "Message Operation!"
            );
          } else {
            this.toast.toastNotification(
              "Something went wrong",
              "Message Operation!"
            );
          }
        });
    }
  }

  checkWeek(lockWeek) {
    return this.commonService.checkWeek(lockWeek);
  }

  @Input() set days_hours(days_hours) {
    if (days_hours) {
      let dayHours = days_hours.days_hours;
      this.totalHours = days_hours.total_hours;
      this.shiftDayhours = Object.entries(dayHours).map(([key, value]) => ({
        date: key,
        count: value,
      }));
    }
  }

  @Input() set dataArray(dataArray: any[]) {
    if (dataArray && this.sites) {
      this._data = dataArray;
      this.sites = this.sites.filter((site) => dataArray.includes(site.id));
      dataArray.forEach((id) => {
        if (!this.sites.some((site) => site.id === id)) {
          const newObject = this.originalArray.find((site) => site.id === id);
          if (newObject) {
            this.sites.push(newObject);
          }
        }
      });
      this.sites.sort((a, b) => a.site_name.localeCompare(b.site_name));
    }
  }

  trackBySiteId(
    index: number,
    item: { id: number; site_name: string }
  ): number {
    return item.id;
  }

  trackByJobRosterId(index: number, job: any): number {
    return job.roster_id;
  }

  JobData: any;
  UnassignedOperationNotes(job) {
    let data = {
      roster_id: job.roster_id,
    };
    this.jobRoster.getNotes(data).subscribe(({ success, data }) => {
      if (success) {
        this.OperationNotes.get("unassignedoperationNotes").setValue(data.operation_notes);
      }
    });

    this.modalRef = this.modalService.open(this.unassignedOperationNotes, {});
    this.JobData = job;
  }

  submitOperationNotes() {
    let data = {
      roster_id: this.JobData.roster_id,
      operation_notes: this.OperationNotes.value.unassignedoperationNotes,
    };
    console.log("Submit data", data);
    this.jobRoster.operationNotes(data).subscribe(
      (res) => {
        let status = "Operation Notes";
        if (res.success) {
          this.toast.toastNotification(res.message, status);
          this.jobroster.sendFilterData()
          this.OperationNotes.reset();
          this.modalRef.close();
        } else {
          this.toast.toastNotification1(res.message, status);
        }
      },
      (error) => {
        this.toast.toastNotification1(this.globals.apiError, "Error");
      }
    );
    this.OperationNotes.value.unassignedoperationNotes = "";
  }

  staffDetails(type, job) {
    const typeToComponent = {
      activity: JobshiftActivityComponent,
      detail: StaffActivityDetailsComponent,
      note: ProfileCommentComponent,
    };
    const selectedComponent = typeToComponent[type];
    let modalRef;
    if (selectedComponent == typeToComponent["note"]) {
      const modalConfig = { centered: true };
      modalRef = this.modalService.open(selectedComponent, modalConfig);
      modalRef.componentInstance.jobroster = "note";
      this.global.guard_id = Number(job.guard_id);
      this.global.roster_id = job.roster_id;
    }
    const modalConfig = { size: "xl" };
    if (selectedComponent == typeToComponent["activity"]) {
      modalRef = this.modalService.open(selectedComponent, modalConfig);
      modalRef.componentInstance.jobStatus = job.status; 
      this.global.roster_id = job.roster_id;
      this.global.guard_id = Number(job.guard_id);
      modalRef.result.then(
        (result) => {
          this.jobroster.sendFilterData()

        },
        (reason) => {
          this.jobroster.sendFilterData()
        }
      );
    }
    if (selectedComponent == typeToComponent["detail"]) {
      const guard_id = Number(job.guard_id);
      let data = {
        document_type: this.document_type,
        guard_id: guard_id,
        start: this.global.start,
      };
      this.rosterService
        .staffData(data)
        .subscribe(({ success, data, total_hours }) => {
          if (success) {
            modalRef = this.modalService.open(selectedComponent, modalConfig);
            modalRef.componentInstance.fromRoster = data;
            modalRef.componentInstance.total_hours = total_hours;
          }
        });
    }
  }

  openSidenav(job, site, day) {
    this.currentDay = day;
    const modalRef: NgbModalRef = this.modalService.open(SplitComponent, {
      backdrop: 'static',
      keyboard: false,
      size: 'lg'
    });
    modalRef.componentInstance.splitData = { shift: job, calenderType: this.calenderType, permissions: this.rosterPermissions, currentDay: day.momentFormat, site: { id: site.id, customer_id: site.customer_id } };
    modalRef.result.then(
      (result) => {
        if (result == 'submit') {
          this.jobroster.sendFilterData()
        }
        console.log('Modal closed with: ', result);
      },
      (reason) => {
        console.log('Dismissed: ', this.getDismissReason(reason));
      }
    );
  }

  copyModal(data){
    const modalRef: NgbModalRef = this.modalService.open(CopyDaywiseShiftsComponent, {
      backdrop: 'static',
      keyboard: false,
      size: 'lg',
      centered: true
    });
    modalRef.componentInstance.copyShiftId = data;
    modalRef.result.then(
      (result) => {
        // if (result) {
        this.jobroster.sendFilterData()
        // }
        console.log('Modal closed with: ', result);
      },
      (reason) => {
        console.log('Dismissed: ', this.getDismissReason(reason));
      }
    );
  }

}

