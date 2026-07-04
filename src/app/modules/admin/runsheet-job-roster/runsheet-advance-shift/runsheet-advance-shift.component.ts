import { ChangeDetectorRef, Component, Input, OnInit } from '@angular/core';
import { FormArray, FormBuilder, FormGroup } from '@angular/forms';
import { faCalendar, faClock, faTrash, faUser } from '@fortawesome/free-solid-svg-icons';
import { ModalDismissReasons, NgbActiveModal, NgbModal } from '@ng-bootstrap/ng-bootstrap';
import { FormServiceOneTwoService } from 'app/services/form-service-one-two.service';
import { RunsheetrosterService } from 'app/services/runsheetroster.service';
import { ToastServiceService } from 'app/services/toast-service.service';
import { GlobalVariable } from 'app/shared/global';
import moment from 'moment';
import { NgxSpinnerService } from 'ngx-spinner';
import { HttpHeaders } from '@angular/common/http';
import { StaffService } from 'app/services/staff.service';
import { AppearanceAnimation, ButtonLayoutDisplay, ButtonMaker, ConfirmBoxInitializer, DialogLayoutDisplay, DisappearanceAnimation } from '@costlydeveloper/ngx-awesome-popup';
import { SiteService } from 'app/services/site.service';
import { CommonOneTwoService } from '../../operations-modules/components/job-roster/common-one-two.service';

@Component({
  selector: 'app-runsheet-advance-shift',
  templateUrl: './runsheet-advance-shift.component.html',
  styleUrls: ['./runsheet-advance-shift.component.scss']
})
export class RunsheetAdvanceShiftComponent implements OnInit {


  selectedStaffName: string = 'N/A';
  selectedStaffPic: string;
  shiftLength: string;
  calenderIcon = faCalendar;
  faClock = faClock;
  faUser = faUser;
  faTrash = faTrash;
  on_call_job;
  shiftEdited;
  clickedboxDate1;
  addShiftAdvanceForm: FormGroup;
  @Input() calenderType;
  @Input() currentDay;
  @Input() site;
  @Input() job;
  guard_id;
  guards = [];
  submitted: boolean;
  uploadFileUrl: any;
  signin_status
  job_status
  fileuploaded: boolean = false;
  uploadFile: File;
  shift_file_name: any;
  addLeaveGuard: boolean = false
  isShiftEdit: boolean = false
  isShowbreak = true;
  closeResult: string;
  isShowCustomRates = true;
  isShowShiftTask = true;
  isShowOthersDetail = true;
  payRate: any = [];
  chargeRate: any = [];

  constructor(private modelService: NgbActiveModal, private formField: FormServiceOneTwoService, private changeDetect: ChangeDetectorRef,
    public global: GlobalVariable, private roster: RunsheetrosterService, private toast: ToastServiceService,
    private commonService: CommonOneTwoService, private spinner: NgxSpinnerService, private staffDoc: StaffService,
    private fb: FormBuilder, private siteService: SiteService,
  ) { }

  ngOnInit(): void {

    console.log("Fetch data from parent", "CalendarType", this.calenderType, "CurrentDay", this.currentDay,
      "Site", this.site)

    this.addShiftAdvanceForm = this.formField.addRunsheetShiftAdvanceForm();

    this.addShiftAdvanceForm.get('payrate_level').valueChanges.subscribe(value => {
      this.siteService.getPayRate(value).subscribe(({ success, data }) => {
        if (success) {
          this.payRate = data
        }
      }, error => {
        this.toast.toastNotification1('Something went wrong. Please contact with support team', 'Error')
      });

    });

    this.addShiftAdvanceForm.get('chargerate_level').valueChanges.subscribe(value => {
      this.siteService.getChargeRate(value).subscribe(({ success, data }) => {
        if (success) {
          this.chargeRate = data
        }
      }, error => {
        this.toast.toastNotification1('Something went wrong. Please contact with support team', 'Error')
      });

    });

    this.getBoxDate(this.currentDay, this.site, this.calenderType)
    this.changeDetect.detectChanges()
  }

  ngAfterViewInit() {
    if (this.job) {
      this.getSpecShift(this.job)
    }
  }

  addTask() {
    this.job_roster_tasks.push(this.fb.group({
      id: [''],
      task_start: [''],
      task_end: [''],
      task: [''],
    }));
  }

  get job_roster_tasks(): FormArray {
    return this.addShiftAdvanceForm.get('run_sheet_job_roster_tasks') as FormArray;
  }

  handleImageError() {
    this.selectedStaffPic = 'assets/images/logo/scouts.png';
  }

  close(data) {
    this.modelService.close(data)
    this.shiftEdited = false
    this.formField.initializeaddShiftAdvanceForm().reset()
    this.changeDetect.markForCheck()
  }

  clearSelection() {
    this.selectedStaffName = null;
    this.selectedStaffPic = null;
    const field = this.calenderType === 'guard' ? 'run_sheet_id' : 'guard_id';
    this.addShiftAdvanceForm.get(field).setValue(null);
    this.getBoxDate(this.currentDay, this.site, this.calenderType)
    setTimeout(() => {
      this.global.selectedStaff = null
    }, 300);
    this.global.selectedStaff = null
    this.changeDetect.detectChanges()
  }

  getBoxDate(day: any, site: any, calType) {
    if (calType == 'run_sheet') {
      this.addShiftAdvanceForm.get('site_name').setValue(site.site_name)
      this.addShiftAdvanceForm.get('run_sheet_id').setValue(site.id)
    }
    else if (calType == 'guard') {
      this.guard_id = site.id
      this.addShiftAdvanceForm.get('guard_name').setValue(site.site_name)
      this.addShiftAdvanceForm.get('guard_id').setValue(site.id)
    }

    this.clickedboxDate1 = moment(day.customFormat, "ddd , DD/MM").format('ddd , DD MMM')
    if (site.customer_id && calType == 'run_sheet') {
      this.getCusGuard(site.id)
    }
    else if (site.guard_id && calType == 'guard') {
      this.getGuardSite(site.guard_id)
    }
  }

  getCusGuard(id) {
    this.roster.getGuardBySite(id).subscribe(({ success, data }) => {
      if (success) {
        this.guards = data
        if (data) {
          data.forEach(element => {
            element.name = element.first_name + (element.middle_name ? ' ' + element.middle_name : '') + (element.last_name ? ' ' + element.last_name : '');
          });
        }
        this.changeDetect.markForCheck()
      }
    }, (error => {
      this.toast.toastNotification1('Something went wrong on getting staff of location', 'Location Staff!')
    }))
  }

  getGuardSite(id) {
    this.roster.getGuardSite(id).subscribe(res => {
      if (res.success) {
        this.guards = res.data
      }
    }, (error => {
      this.toast.toastNotification1('There is something went wrong on fetching locations. Please contact with support team', 'Request Incomplete!')
    }))
  }

  receiveDataFromChildSi(data: any) {
    console.log("advance sites", data)
    this.selectedStaffName = data?.site_name
    this.addShiftAdvanceForm.get('run_sheet_id').setValue(data?.id)
    this.changeDetect.markForCheck()
  }

  receiveDataFromChildSingle(data: any) {
    this.selectedStaffName = data?.value.name
    this.selectedStaffPic = data?.value.profile_image
    this.addShiftAdvanceForm.get('guard_id').setValue(data?.value?.id)
    this.changeDetect.markForCheck()
  }

  onChangeHour(type, event) {
    const start = this.addShiftAdvanceForm.get('start_time').value;
    const end = this.addShiftAdvanceForm.get('end_time').value;
    this.shiftLength = this.commonService.shiftLength(start, end)
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

  addLeave() {
    this.addLeaveGuard = !this.addLeaveGuard
  }

  toggleDisplayDivIf() {
    this.isShowbreak = !this.isShowbreak;
  }

  toggleCustomRates() {
    this.isShowCustomRates = !this.isShowCustomRates;
  }

  toggleTask() {
    this.isShowShiftTask = !this.isShowShiftTask;
  }

  toggleOthers() {
    this.isShowOthersDetail = !this.isShowOthersDetail;
  }

  submitShiftAdvanceForm(status?) {
    const isAdmin = this.global.admin.admin_user_type === 'super-admin';
    const max14Hours = false;
    const shiftLength = parseFloat(this.shiftLength).toFixed(2);
    const result = this.commonService.checkShiftLengthAccess(isAdmin, max14Hours, shiftLength)
    if (!result) {
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
      this.saveRoster(this.addShiftAdvanceForm.value);
      this.addShiftAdvanceForm.markAllAsTouched();
    }
  }

  saveRoster(value): void {
    this.spinner.show();
    const isAdmin = this.global.admin.admin_id;
    const rosterId = localStorage.getItem('runsheet_rosterId');
    const isUpdate = this.addShiftAdvanceForm.value.id ? true : false;
    value.admin = isAdmin;
    value.run_sheet_roster_id = rosterId;

    this.roster[(isUpdate ? 'updateShift' : 'addShift')](value).subscribe(res => {
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
      if (this.addShiftAdvanceForm.value.publish_status == 1 && this.addShiftAdvanceForm.value.run_sheet_roster_id) {
        // this.trackAdmin.storeActivity('Job Roster', this.getRosterActivityMessage(isUpdate, value)).subscribe();
      }

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

  addWithConflict(res, value, type?) {
    const newConfirmBox = new ConfirmBoxInitializer();
    let layoutType;
    layoutType = res.message === 'Sorry Staff On Leave!' ? DialogLayoutDisplay.SUCCESS : DialogLayoutDisplay.DANGER;

    let buttons: ButtonMaker[] = [];

    if (res.hide || res.message === 'Sorry Staff On Leave!') {
      newConfirmBox.setTitle('Access Denied!');
      newConfirmBox.setMessage(res.message);
      layoutType = DialogLayoutDisplay.DANGER;
      if (res.message === 'Sorry Staff On Leave!') {
        buttons.push(new ButtonMaker('Discard', 'Discard', ButtonLayoutDisplay.DANGER));
      }
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
        }
      } else {
        this.spinner.hide();
      }
    });
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

  applyLeave() {
    let data = {
      admin_id: this.global.admin.admin_id,
      reason: this.addShiftAdvanceForm.value.reason,
      id: this.addShiftAdvanceForm.value.id
    }
    this.roster.addLeave(data).subscribe(({ success, message }) => {
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
          this.roster.delShiftTask(data).subscribe(({ success, message }) => {
            let status = 'Shift Task Operation!'
            if (success) {
              (this.addShiftAdvanceForm.get('run_sheet_job_roster_tasks') as FormArray).removeAt(index);
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
      (this.addShiftAdvanceForm.get('run_sheet_job_roster_tasks') as FormArray).removeAt(index);
      this.changeDetect.markForCheck()
    }

  }

  getSpecShift(id) {
    this.shiftEdited = true
    this.spinner.show()
    this.roster.getSpecShift(id).subscribe(({ success, data }) => {
      if (success) {
        this.selectedStaffName = data?.unprofile_name ?? this.selectedStaffName;
        this.addShiftAdvanceForm.get('id').setValue(data.id)
        const guardIdInt = parseInt(data?.guard_id, 10);
        const siteIdInt = parseInt(data?.run_sheet_id, 10);
        this.global.selectedStaff = this.calenderType === 'guard' ? siteIdInt : guardIdInt;

        this.addShiftAdvanceForm.get('site_name').setValue(data.run_sheet_name)
        this.addShiftAdvanceForm.get('run_sheet_id').setValue(siteIdInt)
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
        const num = parseInt(data.chargerate, 10);
        const num1 = parseInt(data.payrate, 10);
        this.addShiftAdvanceForm.get('custome_rate').setValue(data.custome_rate)
        this.addShiftAdvanceForm.get('chargerate').setValue(num)
        this.addShiftAdvanceForm.get('chargerate_level').setValue(data.chargerate_level)
        this.addShiftAdvanceForm.get('payrate_level').setValue(data.payrate_level)
        this.addShiftAdvanceForm.get('payrate').setValue(num1)
        this.addShiftAdvanceForm.get('un_published_shift').setValue(data.un_published_shift)
        this.addShiftAdvanceForm.get('covid_marshal').setValue(data.covid_marshal)
        this.addShiftAdvanceForm.get('public_holidays').setValue(data.public_holidays)
        this.addShiftAdvanceForm.get('training').setValue(data.training)
        this.addShiftAdvanceForm.get('continuation').setValue(data.continuation)
        this.addShiftAdvanceForm.get('over_time').setValue(data.over_time)
        this.addShiftAdvanceForm.get('travel_time').setValue(data.travel_time)
        this.addShiftAdvanceForm.get('custome_chagerate').setValue(data.custome_chagerate)
        this.addShiftAdvanceForm.get('custome_payrate').setValue(data.custome_payrate)
        this.addShiftAdvanceForm.get('job_instruction_text').setValue(data.job_instruction_text)
        this.on_call_job = data.on_call_job
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
              task: [element.task],
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
      }

    }, (error => {
      this.spinner.hide()
      this.toast.toastNotification1('Something went wrong. Please contact with suppot team', 'Request Incomplete!')
    }))
  }

}
