import { AfterViewInit, ChangeDetectionStrategy, ChangeDetectorRef, Component, ElementRef, Input, OnDestroy, OnInit, TemplateRef, ViewChild } from '@angular/core';
import { GlobalVariable } from 'app/shared/global';
import moment from 'moment';
import { ModalDismissReasons, NgbActiveModal, NgbModal, NgbModalRef } from '@ng-bootstrap/ng-bootstrap';
import { FormArray, FormBuilder, FormGroup, Validators } from '@angular/forms';
import { JobRoster1Service } from 'app/services/job-roster1.service';
import { ToastServiceService } from 'app/services/toast-service.service';
import { FormServiceOneTwoService } from 'app/services/form-service-one-two.service';
import { TrackAdminActivityService } from 'app/services/track-admin-activity.service';
import { NgxSpinnerService } from 'ngx-spinner';
import { faCalendar, faClock, faUser, faTrash, faCreditCard, faPencil, faXmark, faPhone, faMessage } from '@fortawesome/free-solid-svg-icons';
import { HttpHeaders } from '@angular/common/http';
import { StaffService } from 'app/services/staff.service';
import { AppearanceAnimation, ButtonLayoutDisplay, ButtonMaker, ConfirmBoxInitializer, DialogLayoutDisplay, DisappearanceAnimation } from '@costlydeveloper/ngx-awesome-popup';
import { SiteService } from 'app/services/site.service';
import { CommonOneTwoService } from '../common-one-two.service';
import { RosterServiceService } from '../roster-service.service';
import { ActivatedRoute } from '@angular/router';
import { CheckAvailableStaffComponent } from 'app/modules/admin/models/check-available-staff/check-available-staff.component';
import { SelectMultipleGuardsComponent } from 'app/modules/admin/component/select-multiple-guards/select-multiple-guards.component';
import { JobRosterComponent } from '../job-roster.component';

@Component({
  selector: 'app-advance-shift',
  templateUrl: './advance-shift.component.html',
  styleUrls: ['./advance-shift.component.scss'],
  changeDetection: ChangeDetectionStrategy.OnPush,
})

export class AdvanceShiftComponent implements OnInit, AfterViewInit, OnDestroy {

  /**font awesome icon */
  faXmark = faXmark;
  faPencil = faPencil;
  faCreditCard = faCreditCard;
  faTrash = faTrash;
  calenderIcon = faCalendar;
  faClock = faClock;
  faUser = faUser;
  phone = faPhone
  msg = faMessage

  @ViewChild('selectGuards') selectGuardsComponent: SelectMultipleGuardsComponent;
  @ViewChild("sendOtp") sendOtp: TemplateRef<any>;
  modalMessage :string = ''
  otpModalmessage: string = ''
  otpValue: string = '';
  private pendingValue: any = null;
  private pendingType: string | undefined = undefined;
  
  selectedStaffPic: string;
  addShiftAdvanceForm: FormGroup;
  @Input() calenderType;
  @Input() site;
  @Input() currentDay;
  @Input() job;
  @Input() permissions;
  @Input() accessJobRoaster;
  submitted: boolean;
  uploadFileUrl: any;
  fileuploaded: boolean = false
  clickedboxDate1
  shiftEdited
  isShowOthersDetail = true;
  isShowShiftTask = true;
  isShowCustomRates = true;
  isShowbreak = true;
  shift_paste: boolean = false
  shift_select: boolean = true
  isShiftEdit: boolean = false
  addLeaveGuard: boolean = false
  closeResult: string;
  signin_status
  job_status
  shiftLength: string;
  selectedStaffName: string = 'N/A'
  uploadFile: File;
  shift_file_name: any;
  guards = [];
  guard_id
  roster_id
  chargeRate: any = []
  payRate: any = []
  on_call_job;
  admin_confirm;
  @Input() jobRosterId: string;
  siteSelected: boolean = false;
  submitButtonClicked: boolean = false;
  modalRef: NgbModalRef;

  constructor(private modelService: NgbActiveModal, private fb: FormBuilder,
    private formField: FormServiceOneTwoService, private spinner: NgxSpinnerService,
    public global: GlobalVariable, private toast: ToastServiceService,
    private modalService: NgbModal, private staffDoc: StaffService,
    private rosterService: JobRoster1Service, private trackAdmin: TrackAdminActivityService,
    private changeDetect: ChangeDetectorRef, private siteService: SiteService, private commonService: CommonOneTwoService,
    private rosterServices: RosterServiceService, private route: ActivatedRoute,) 
  { }

  ngOnDestroy(): void {
    this.global.selectedStaff = []
  }

  ngOnInit(): void {

    this.addShiftAdvanceForm = this.formField.initializeaddShiftAdvanceForm()
    this.addShiftAdvanceForm.get('payrate_level').valueChanges.subscribe(value => {
      this.siteService.getPayRate(value).subscribe(({ success, data }) => {
        if (success) {
          this.payRate = data
        }
      }, error => {
        this.toast.toastNotification1('Something went wrong. Please contact with support team', 'Error')
      });
    });

    // this.addShiftAdvanceForm.get('chargerate_level').valueChanges.subscribe(value => {
    //   this.siteService.getChargeRate(value).subscribe(({ success, data }) => {
    //     if (success) {
    //       this.chargeRate = data
    //     }
    //   }, error => {
    //     this.toast.toastNotification1('Something went wrong. Please contact with support team', 'Error')
    //   });
    // });

    this.getBoxDate(this.currentDay, this.site, this.calenderType)
    this.watchReimbursementControl();
    this.changeDetect.detectChanges();
    // console.log('Job ID:', this.jobRosterId);
  }

  ngAfterViewInit() {
    if (this.job) {
      this.getSpecRoster(this.job)
    }
  }

  receiveDataFromChildSingle(data: any) {
    this.selectedStaffName = data?.value.name
    this.selectedStaffPic = data?.value.profile_image
    this.addShiftAdvanceForm.get('guard_id').setValue(data?.value?.id)
    this.changeDetect.markForCheck()
  }

  receiveDataFromChildSi(data: any) {
    this.selectedStaffName = data?.site_name
    if (data.site_name) {
      this.siteSelected = true;
      this.addShiftAdvanceForm.get('site_id').setValue(data?.site_id)
      this.changeDetect.markForCheck()
      this.submitButtonClicked = true;
    }
    else {
      this.siteSelected = false;
    }
  }

  ////add shift task 
  removeTextArea(value, index) {
    if (value.id) {
      const newConfirmBox = new ConfirmBoxInitializer();
      newConfirmBox.setTitle('Confirm Action');
      newConfirmBox.setMessage('Are you sure you want to confirm this action?');
      newConfirmBox.setConfig({
        layoutType: DialogLayoutDisplay.DANGER, // SUCCESS | INFO | NONE | DANGER | WARNING
        animationIn: AppearanceAnimation.SWING, // BOUNCE_IN | SWING | ZOOM_IN | ZOOM_IN_ROTATE | ELASTIC | JELLO | FADE_IN | SLIDE_IN_UP | SLIDE_IN_DOWN | SLIDE_IN_LEFT | SLIDE_IN_RIGHT | NONE
        animationOut: DisappearanceAnimation.BOUNCE_OUT, // BOUNCE_OUT | ZOOM_OUT | ZOOM_OUT_WIND | ZOOM_OUT_ROTATE | FLIP_OUT | SLIDE_OUT_UP | SLIDE_OUT_DOWN | SLIDE_OUT_LEFT | SLIDE_OUT_RIGHT | NONE
        allowHtmlMessage: true,
        buttonPosition: 'right', // optional 
      });
      newConfirmBox.setButtonLabels('Confirm', 'Decline');
      newConfirmBox.openConfirmBox$().subscribe(resp => {
        if (resp.success) {
          let data = {
            id: value.id,
            admin_id: this.global.admin.admin_id
          }
          this.rosterService.delShiftTask(data).subscribe(({ success, message }) => {
            let status = 'Shift Task Operation!'
            if (success) {
              (this.addShiftAdvanceForm.get('job_roster_tasks') as FormArray).removeAt(index);
              this.toast.toastNotification1(message, status)
              this.changeDetect.markForCheck()
            }
            else {
              this.toast.toastNotification1(message, status)
            }
          }, error => {
            this.toast.toastNotification1('Something went wrong. Please contact with support team.', 'Request Incomplete!')
          });
        }
      });
    }
    else {
      (this.addShiftAdvanceForm.get('job_roster_tasks') as FormArray).removeAt(index);
      this.changeDetect.markForCheck()
    }
  }

  get job_roster_tasks(): FormArray {
    return this.addShiftAdvanceForm.get('job_roster_tasks') as FormArray;
  }

  addTask() {
    this.job_roster_tasks.push(this.fb.group({
      id: [''],
      task_start: [''],
      task_end: [''],
      task: ['', Validators.required],
    }));
  }

  getBoxDate(day: any, site: any, calType) {
    const form = this.addShiftAdvanceForm;
    if (calType === 'location') {
      form.get('site_name').setValue(site.site_name);
      form.get('site_id').setValue(site.id);
      if (site.customer_id) {
        this.getCusGuard(site.id);
      }
    } else if (calType === 'staff') {
      this.guard_id = site.id;
      form.get('guard_name').setValue(site.site_name);
      form.get('guard_id').setValue(site.id);
      this.getGuardSite(site.id);
    }
    this.clickedboxDate1 = moment(day.customFormat, "ddd , DD/MM").format('ddd , DD MMM');
  }

  getCusGuard(id) {
    this.rosterServices.getGuardBySite(id, this.currentDay).subscribe(({ success, data, is_holiday }) => {
      if (success) {
        if (data) {
          data.forEach(element => {
            element.name = element.first_name + (element.middle_name ? ' ' + element.middle_name : '') + (element.last_name ? ' ' + element.last_name : '');
          });
        }
        this.guards = data
        this.addShiftAdvanceForm.get('public_holidays').setValue(is_holiday)
        this.changeDetect.markForCheck()
      }
    }, (error => {
      this.toast.toastNotification1('Something went wrong on getting staff of location', 'Location Staff!')
    }))
  }

  getGuardSite(id) {
    this.rosterServices.getGuardSite(id, this.currentDay).subscribe(({ success, data, is_holiday }) => {
      if (success) {
        this.guards = data
      }
      this.addShiftAdvanceForm.get('public_holidays').setValue(is_holiday)
    }, (error => {
      this.toast.toastNotification1('Something went wrong on getting location of staff', 'Location Staff!')
    }))
  }

  /*Submit the advance shift form Model*/
  submitShiftAdvanceForm(status?) {
    const isAdmin = this.global.admin.admin_user_type === 'super-admin';
    const max14Hours = this.permissions?.max14Hours || false;
    const shiftLength = parseFloat(this.shiftLength).toFixed(2);
    const result = this.commonService.checkShiftLengthAccess(isAdmin, max14Hours, shiftLength)
    if (!result) {
      return
    }
    if (this.calenderType == 'staff' && !this.siteSelected) {
      this.submitButtonClicked = true;
      return
    }
    if (this.addShiftAdvanceForm.value.start_time || this.addShiftAdvanceForm.value.end_time) {
      const object = this.global.AddTimeDate(this.addShiftAdvanceForm.value.start_time, this.addShiftAdvanceForm.value.end_time, this.currentDay?.momentFormat);
      this.addShiftAdvanceForm.get('start').setValue(object.start);
      this.addShiftAdvanceForm.get('end').setValue(object.end);
      if (object.status && object.status === 'null') {
        this.submitted = false;
        object.status = 'Shift Operation!';
        this.toast.toastNotification1(object.msg, object.status);
      } else {
        this.submitted = true;
      }
    }

    if (this.submitted) {
      this.addShiftAdvanceForm.value.job_instrcutions = this.uploadFileUrl || '';
      this.addShiftAdvanceForm.value.publish_status = status ? 1 : 0;
      this.addShiftAdvanceForm.value.admin_id = JSON.parse(localStorage.getItem('admin')).admin_id;
      if (this.addShiftAdvanceForm.get('custome_chagerate').value) {
        const manualChargeRateResult = this.global.manualChargeRate.reduce((obj, item) => {
          obj[item.chargerateName] = item.regional;
          return obj;
        }, {});
        this.addShiftAdvanceForm.value.manualChargeRate = manualChargeRateResult;
      }

      if (this.addShiftAdvanceForm.get('custome_payrate').value) {
        const manualPayRateResult = this.global.manualPayRate.reduce((obj, item) => {
          obj[item.payrateName] = item.regional;
          return obj;
        }, {});
        this.addShiftAdvanceForm.value.manualPayRate = manualPayRateResult;
      }
      this.addShiftAdvanceForm.value.on_call_job = this.on_call_job;
      this.addShiftAdvanceForm.value.admin_confirm =  this.admin_confirm ?? 0;
      this.addShiftAdvanceForm.value.save = 'save'
      this.addShiftAdvanceForm.value.tempDate = moment(this.addShiftAdvanceForm.value.start).format('YYYY-MM-DD');
      if(this.addShiftAdvanceForm.value.adhoc_shift === true){
        this.addShiftAdvanceForm.value.adhoc_shift = 'adhoc'
      }
      else {
        this.addShiftAdvanceForm.value.adhoc_shift = 'no'
      }
      if(this.addShiftAdvanceForm.value.un_published_shift === true){
        this.addShiftAdvanceForm.value.un_published_shift = 1
      }
      else {
        this.addShiftAdvanceForm.value.un_published_shift = 0
      }
      console.log("Submit data", this.addShiftAdvanceForm.value)
      this.saveRoster(this.addShiftAdvanceForm.value);
      this.addShiftAdvanceForm.markAllAsTouched();
    }
  }

  saveRoster(value): void {
    this.spinner.show();
    const isAdmin = this.global.admin.admin_id;
    // const rosterId = localStorage.getItem('rosterId');
    const isUpdate = this.addShiftAdvanceForm.value.id ? true : false;
    value.admin = isAdmin;
    if(this.jobRosterId){
      value.roster_id = this.jobRosterId;
    }
    else {
      value.roster_id = this.accessJobRoaster;
    }
    this.rosterService[(isUpdate ? 'updateShift' : 'addShift')](value).subscribe(res => {
      const status = 'Job Roster Operation';
      this.handleRosterResponse(res, status, isUpdate, value);
    }, (error) => {
      this.spinner.hide();
      this.toast.toastNotification1('Something went wrong. Please contact the support team.', 'Request Incomplete');
    });
  }

  handleRosterResponse(res, status, isUpdate, value): void {
    if (res.success) {
      this.toast.toastNotification(res.message, status);
      this.trackAdmin.storeActivity('Job Roster', this.getRosterActivityMessage(isUpdate, value), localStorage.getItem('routerId')).subscribe();
      this.close(res.success);
      this.submitted = false;
      this.global.timestamp = Date.now();
      this.fileuploaded = false;
    } else {
      this.addWithConflict(res, value, 'saveRoster');
    }
    this.global.timestamp = Date.now();
    this.spinner.hide();
    this.changeDetect.markForCheck()
  }

  // confirm conflict shift for add
  addWithConflict(res, value, type?) {
    const newConfirmBox = new ConfirmBoxInitializer();
    let layoutType
    layoutType = res.message !== 'Sorry Staff On Leave!' ? DialogLayoutDisplay.SUCCESS : DialogLayoutDisplay.DANGER;
    let buttons: ButtonMaker[] = []
    if (res.hide || res.type === 'document') {
      newConfirmBox.setTitle('Access Denied!');
      newConfirmBox.setMessage(res.message);
      layoutType = DialogLayoutDisplay.DANGER;
      buttons.push(new ButtonMaker('Discard', 'Discard', ButtonLayoutDisplay.DANGER));
    }
    else if(res.require_otp){
      this.showOtpVerificationModal(res, value, type);
      return;
    }
    else {
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
          // else if (type === 'paste') this.savePaste(value);
          // else if (type === 'copy') this.dragDropApi(value);
        }
      } else {
        this.spinner.hide();
        // this.jobroster.sendFilterData();
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
        // this.jobroster.sendFilterData()
      },
      (reason) => {
        this.resetPendingData();
        this.spinner.hide();
        // this.jobroster.sendFilterData();
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

    if (this.pendingType === 'saveRoster') {
      this.saveRoster(this.pendingValue);
    }

    this.modalRef?.close();
    this.resetPendingData();
  }

  onChangeHour(type, event) {
    const start = this.addShiftAdvanceForm.get('start_time').value;
    const end = this.addShiftAdvanceForm.get('end_time').value;
    this.shiftLength = this.commonService.shiftLength(start, end)
  }

  checkStaffAvailbility() {
    const modalRef = this.modalService.open(CheckAvailableStaffComponent, {
      size: 'lg',
      centered: true,
      backdropClass: 'modal-no-backdrop',
      backdrop: 'static',
      windowClass: "modal-right-staff-avail",
    });
    modalRef.componentInstance.fromParent = this.addShiftAdvanceForm;
    modalRef.componentInstance.date = this.currentDay;
    modalRef.componentInstance.modalRef = modalRef;
    modalRef.result.then(
      (result) => {
        this.closeResult = `Closed with: ${result}`;
      },
      (reason) => {
        this.selectedStaffName = reason.first_name + " " + reason.last_name
        this.selectedStaffPic = `https://apis.thescouts.com.au/guard/${reason?.profile_image}`
        this.getBoxDate(this.currentDay, this.site, this.calenderType)
        this.addShiftAdvanceForm.get('guard_id').setValue(reason.id)
        this.closeResult = `Dismissed ${this.getDismissReason(reason)}`;
        this.global.updateSelectedOption(reason.id);
        this.changeDetect.detectChanges()
      },
    );
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

  toggleTask() {
    this.isShowShiftTask = !this.isShowShiftTask;
  }

  toggleOthers() {
    this.isShowOthersDetail = !this.isShowOthersDetail;
  }

  toggleCustomRates() {
    this.isShowCustomRates = !this.isShowCustomRates;
  }

  toggleDisplayDivIf() {
    this.isShowbreak = !this.isShowbreak;
  }

  handleImageError() {
    this.selectedStaffPic = 'assets/images/logo/scouts.png';
  }

  handleFileInput() {
    let input = document.createElement("input");
    input.type = "file";
    input.accept = "image/*,application/pdf";
    input.onchange = (_) => {
      let files = Array.from(input.files);
      if (!files || files.length === 0) return;
      this.uploadFile = files[0];
      const myFormData = new FormData();
      const headers = new HttpHeaders({
        'AuthorizationToken': 'Bearer ' + localStorage.getItem('accessToken')
      })
      myFormData.append('file', this.uploadFile, this.uploadFile.name);
      myFormData.append('folder', 'job_roster')
      this.staffDoc.uploadImgPdf(myFormData, {
        headers: headers
      }).subscribe(
        response => {
          if (response.success) {
            this.fileuploaded = true
            this.shift_file_name = response.path
            this.uploadFileUrl = response.url
            this.changeDetect.markForCheck()
          }
          else {
            this.toast.toastNotification1(response.message, 'Request Incomplete!')
          }
        },
        (error) => {
          this.toast.toastNotification1('Something went wrong. Please contact with support team.', 'Request Incomplete!')
        }
      );
    };
    input.click();
  }

  close(data) {
    this.modelService.close(data)
    this.shiftEdited = false
    this.formField.initializeaddShiftAdvanceForm().reset()
    this.changeDetect.markForCheck()
  }

  getRosterActivityMessage(isUpdate, value): string {
    const name = this.addShiftAdvanceForm.value.first_name + ' ' + this.addShiftAdvanceForm.value.last_name;
    const startEnd = this.addShiftAdvanceForm.value.start + '-' + this.addShiftAdvanceForm.value.end
    if (isUpdate) {
      return `Update shift of ${name} start and end is: ${startEnd}`;
    } else {
      return `Add shift of ${name ?? 'Unassign'} start and end is: ${startEnd}`;
    }
  }

  // this section for guard leave
  addLeave() {
    this.addLeaveGuard = !this.addLeaveGuard
  }

  applyLeave() {
    let data = {
      admin_id: this.global.admin.admin_id,
      reason: this.addShiftAdvanceForm.value.reason,
      id: this.addShiftAdvanceForm.value.id
    }
    this.rosterService.addLeave(data).subscribe(({ success, message }) => {
      if (success) {
        this.toast.toastNotification(message, 'Leave Operation!')
        this.clearSelection()
      }
      else {
        this.toast.toastNotification(message, 'Leave Operation!')
      }
      this.changeDetect.markForCheck()
    }, error => {
      this.toast.toastNotification1('Something went wrong. Please contact with support team', 'Request Incomplete!')
    })
  }

  viewFile() {
    window.open(this.uploadFileUrl, '_blank')
  }

  validateNumber(event: any) {
    const input = event.target.value;
    const pattern = /^[0-9]+(\.[0-9]{1,2})?$/;
    const numericValue = parseFloat(input);
    if (!pattern.test(input) || numericValue > 2) {
      event.preventDefault();
      this.addShiftAdvanceForm.get('travel_time_value').setValue(input.substring(0, input.length - 1));
    } else {}
  }

  clearSelection() {
    this.selectedStaffName = null;
    this.selectedStaffPic = null;
    const field = this.calenderType === 'staff' ? 'site_id' : 'guard_id';
    if (this.selectGuardsComponent) {
      this.selectGuardsComponent.resetSelection();
    }
    this.addShiftAdvanceForm.get(field).setValue('');
    this.getBoxDate(this.currentDay, this.site, this.calenderType)
    this.global.updateSelectedOption(null)
    this.changeDetect.detectChanges()
  }

  getSpecRoster(id) {
    this.shiftEdited = true
    this.spinner.show()
    this.rosterService.getSpecRoster(id).subscribe(({ success, data }) => {
      if (success) {
        this.selectedStaffName = data?.unprofile_name ?? this.selectedStaffName;
        this.addShiftAdvanceForm.get('id').setValue(data.id)
        const guardIdInt = parseInt(data?.guard_id, 10);
        const siteIdInt = parseInt(data?.site_id, 10);
        this.global.selectedStaff = this.calenderType === 'staff' ? siteIdInt : guardIdInt;
        this.global.updateSelectedOption(this.global.selectedStaff);
        this.addShiftAdvanceForm.get('site_name').setValue(data.site_name)
        this.addShiftAdvanceForm.get('site_id').setValue(siteIdInt)
        const start = moment(data.start, 'DD-MM-YYYY HH:mm').format('HH:mm');
        const end = moment(data.end, 'DD-MM-YYYY HH:mm').format('HH:mm');
        this.addShiftAdvanceForm.get('guard_id').setValue(data?.guard_id)
        this.addShiftAdvanceForm.get('start_time').setValue(start)
        this.addShiftAdvanceForm.get('end_time').setValue(end)
        this.addShiftAdvanceForm.get('shift_chargeable').setValue(data.shift_chargeable)
        this.addShiftAdvanceForm.get('shift_payable').setValue(data.shift_payable)
        this.addShiftAdvanceForm.get('unprofile_name').setValue(data.unprofile_name)
        this.addShiftAdvanceForm.get('po_wo').setValue(data.po_wo)
        this.addShiftAdvanceForm.get('shift_payable').setValue(data.shift_payable)
        // const num = parseInt(data.chargerate, 10);
        const num1 = parseInt(data.payrate, 10);
        this.addShiftAdvanceForm.get('custome_rate').setValue(data.custome_rate)
        // this.addShiftAdvanceForm.get('chargerate').setValue(num)
        // this.addShiftAdvanceForm.get('chargerate_level').setValue(data.chargerate_level)
        this.addShiftAdvanceForm.get('payrate_level').setValue(data.payrate_level)
        this.addShiftAdvanceForm.get('payrate').setValue(num1)
        this.addShiftAdvanceForm.get('un_published_shift').setValue(data.un_published_shift)
        this.addShiftAdvanceForm.get('covid_marshal').setValue(data.covid_marshal)
        this.addShiftAdvanceForm.get('public_holidays').setValue(data.public_holidays)
        this.addShiftAdvanceForm.get('training').setValue(data.training)
        // this.addShiftAdvanceForm.get('continuation').setValue(data.continuation)
        this.addShiftAdvanceForm.get('over_time').setValue(data.over_time)
        this.addShiftAdvanceForm.get('reimbursement').setValue(data.reimbursement)
        this.addShiftAdvanceForm.get('reimbursement_text').setValue(data.reimbursement_text)
        this.addShiftAdvanceForm.get('reimbursement_value').setValue(data.reimbursement_value)
        this.addShiftAdvanceForm.get('travel_time').setValue(data.travel_time)
        this.addShiftAdvanceForm.get('custome_chagerate').setValue(data.custome_chagerate)
        this.addShiftAdvanceForm.get('custome_payrate').setValue(data.custome_payrate)
        this.addShiftAdvanceForm.get('job_instruction_text').setValue(data.job_instruction_text)
        this.addShiftAdvanceForm.get('adhoc_shift').setValue(data.adhoc_shift)
        this.on_call_job = data.on_call_job
        this.admin_confirm = data.admin_confirm
        this.signin_status = data.signin_status
        this.job_status = data.job_status
        if (data.job_instrcutions && data.job_instrcutions != null) {
          this.fileuploaded = true
          this.shift_file_name = 'View Job Instruction File'
          this.uploadFileUrl = data.job_instrcutions
        }
        this.isShiftEdit = !!data.guard_id;
        this.isShowCustomRates = !!data.custome_rate;
        this.addShiftAdvanceForm.get('over_time_value').setValue(data.over_time_value ?? '');
        this.addShiftAdvanceForm.get('travel_time_value').setValue(data.travel_time_value ?? '');
        if (data.jobRosterTasks) {
          while (this.job_roster_tasks.length > 0) {
            this.job_roster_tasks.removeAt(0);
          }
          this.isShowShiftTask = false
          data.jobRosterTasks.forEach(element => {
            const start = moment(element.task_start, 'DD-MM-YYYY HH:mm').format('HH:mm');
            const end = moment(element.task_end, 'DD-MM-YYYY HH:mm').format('HH:mm');
            this.job_roster_tasks.push(this.fb.group({
              id: [element.id],
              task_start: [start],
              task_end: [end],
              task: [element.task, Validators.required],
            }));
          });
        }

        if (data.custome_payrate && data.manualPayRate) {
          this.global.manualPayRate.forEach((item) => {
            item.regional = data.manualPayRate[item.payrateName];
          });
        }

        if (data.custome_chagerate && data.manualChargeRate) {
          this.global.manualChargeRate.forEach((item) => {
            item.regional = data.manualChargeRate[item.chargerateName];
          });
        }
        const startTime = moment(start, 'HH:mm');
        const endTime = moment(end, 'HH:mm');
        this.shiftLength = this.commonService.shiftLength(startTime, endTime)
        this.spinner.hide()
        this.changeDetect.detectChanges()
        this.changeDetect.markForCheck()
      }

    }, (error => {
      this.spinner.hide()
      this.toast.toastNotification1('Something went wrong. Please contact with suppot team', 'Request Incomplete!')
    }))
  }

  onDiscountKeyDown(event: KeyboardEvent): void {
    if (event.key === '-' || event.key === '+') {
      event.preventDefault();
    }
  }

  checkWeek(lockWeek) {
    return this.commonService.checkWeek(lockWeek)
  }

  watchReimbursementControl() {
    this.addShiftAdvanceForm.get('reimbursement').valueChanges.subscribe(checked => {
      if (!checked) {
        this.addShiftAdvanceForm.get('reimbursement_text').reset();
        this.addShiftAdvanceForm.get('reimbursement_value').reset();
      }
    });
  }
}
