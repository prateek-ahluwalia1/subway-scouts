import { ChangeDetectionStrategy, ChangeDetectorRef, Component, EventEmitter, Input, OnInit, Output, TemplateRef, ViewChild } from '@angular/core';
import { RosterServiceService } from '../roster-service.service';
import { ToastServiceService } from 'app/services/toast-service.service';
import { GlobalVariable } from 'app/shared/global';
import { AbstractControl, FormGroup } from '@angular/forms';
import { FormServiceOneTwoService } from 'app/services/form-service-one-two.service';
import moment from 'moment';
import { NgxSpinnerService } from 'ngx-spinner';
import { AppearanceAnimation, ButtonLayoutDisplay, ButtonMaker, ConfirmBoxInitializer, DialogLayoutDisplay, DisappearanceAnimation } from '@costlydeveloper/ngx-awesome-popup';
import { JobRosterComponent } from '../job-roster.component';
import { AdvanceShiftComponent } from '../advance-shift/advance-shift.component';
import { ModalDismissReasons, NgbModal, NgbModalRef } from '@ng-bootstrap/ng-bootstrap';
import { PermissionsService } from 'app/services/permissions.service';
import { CommonOneTwoService } from '../common-one-two.service';
import { ActivatedRoute } from '@angular/router';

@Component({
  selector: 'app-basic-shift-popover',
  templateUrl: './basic-shift-popover.component.html',
  styleUrls: ['./basic-shift-popover.component.scss'],
  changeDetection: ChangeDetectionStrategy.OnPush,

})
export class BasicShiftPopoverComponent implements OnInit {

  modalRef: NgbModalRef;
  @Input() calenderType: string;
  @Input() site: any;
  @Input() day: any;
  currentDay
  selectedSiteBox
  public selectedValue: any;

  addShiftBasicForm: FormGroup;
  guard_id: any;
  clickedboxDate: string;
  clickedboxDate1: string;
  guards: any = [];
  public filteredList: any = [];
  selectedStaffName: string = 'N/A'
  selectedStaffPic: any;
  rosterPermissions: any;
  shiftLength: string;
  guardID:any
  isPublicHoliday:boolean =false;
  jobRosterId: string;

  @ViewChild('partTimeGuardDialog') partTimeGuardDialog!: TemplateRef<any>;
  @ViewChild("sendOtp") sendOtp: TemplateRef<any>;
  modalMessage :string = ''
  otpModalmessage: string = ''
  otpValue: string = '';
  private pendingValue: any = null;
  private pendingType: string | undefined = undefined;

  constructor(private rosterService: RosterServiceService, private toast: ToastServiceService, private global: GlobalVariable,
    private formField: FormServiceOneTwoService, private spinner: NgxSpinnerService, private jobroster: JobRosterComponent,
    private modalService: NgbModal, private permissionService: PermissionsService, private commonService: CommonOneTwoService,
    private cdr: ChangeDetectorRef, private route: ActivatedRoute) { }

  ngOnInit(): void {
    const per = this.permissionService.getPermissionsByTitle('WFM Tools');
    this.rosterPermissions = per?.childPage?.find(item => item.title === 'Staff Roster/Scheduling');
    this.addShiftBasicForm = this.formField.initializeAddShiftBasicForm()
    this.filteredList = this.guards;

    this.route.paramMap.subscribe(params => {
      this.jobRosterId = params.get('id');
      console.log('Job ID:', this.jobRosterId);
    });


  }
  get f(): { [key: string]: AbstractControl } {
    return this.addShiftBasicForm.controls;
  }

  submitted: boolean = false
  showLocationError: boolean = false;
  onSubmit(p) {
    const isAdmin = this.global.admin.admin_user_type === 'super-admin';
    const max14Hours = this.rosterPermissions?.max14Hours || false;
    const shiftLength = parseFloat(this.shiftLength).toFixed(2);
    const result = this.commonService.checkShiftLengthAccess(isAdmin, max14Hours, shiftLength)
    if (!result) {
      return
    }
    if (this.addShiftBasicForm.value.start_time || this.addShiftBasicForm.value.end_time) {
      let object = this.global.AddTimeDate(this.addShiftBasicForm.value.start_time, this.addShiftBasicForm.value.end_time, this.currentDay?.momentFormat)
      this.addShiftBasicForm.get('start').setValue(object.start)
      this.addShiftBasicForm.get('end').setValue(object.end)
      if (object.status && object.status == 'null') {
        this.submitted = false;
        object.status = 'Shift Operation!'
        this.toast.toastNotification1(object.msg, object.status)
      }
      else {
        this.submitted = true
      }
    }
    if (this.calenderType === 'staff' && !this.selectedValue) {
      this.showLocationError = true;
      return;
    }
    if (this.submitted) {
      p.close()
      if (this.calenderType === 'staff') {
        this.addShiftBasicForm.get('guard_id').setValue(this.guard_id)
      }
      this.addShiftBasicForm.value.publish_status = 0
      this.addShiftBasicForm.value.admin_id = this.global.admin.admin_id
      this.addShiftBasicForm.value.tempDate = moment(this.addShiftBasicForm.value.start).format('YYYY-MM-DD');
      this.addShiftBasicForm.value.save = 'save'
      this.addShiftBasicForm.value.adhoc_shift = 'no'
      this.saveRoster(this.addShiftBasicForm.value)
    }
  }

  saveRoster(value): void {
    this.spinner.show();
    const isAdmin = this.global.admin.admin_id;
    // const rosterId = localStorage.getItem('rosterId');
    value.admin = isAdmin;
    // value.roster_id = rosterId;
    value.roster_id = this.jobRosterId;
    this.rosterService[('addShift')](value).subscribe(res => {
      const status = 'Job Roster Operation';
      this.handleRosterResponse(res, status, value);
      this.addShiftBasicForm.reset();
    }, (error) => {
      this.spinner.hide();
      this.toast.toastNotification1('Something went wrong. Please contact the support team.', 'Request Incomplete');
    });
  }

  handleRosterResponse(res, status, value): void {
    if (res.success) {
      this.toast.toastNotification(res.message, status);
      this.formField.initializeAddShiftBasicForm().reset();
      this.addShiftBasicForm.get('start_time').setValue('00:00');
      this.addShiftBasicForm.get('end_time').setValue('00:00');
      this.selectedValue = '';
      this.jobroster.sendFilterData();
      this.submitted = false;
      this.global.timestamp = Date.now();
    } else {
      this.addWithConflict(res, value, 'saveRoster');
    }

    this.global.timestamp = Date.now();
    this.spinner.hide();
  }
  addWithConflict(res, value, type?) {
    const newConfirmBox = new ConfirmBoxInitializer();
    let layoutType
    layoutType = res.message !== 'Sorry Staff On Leave!' ? DialogLayoutDisplay.SUCCESS : DialogLayoutDisplay.DANGER;

    let buttons: ButtonMaker[] = [];
    if (res.hide || res.message === 'Sorry Staff On Leave!' || res.type === 'document') {
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
        this.jobroster.sendFilterData()
      },
      (reason) => {
        // dismissed (Cancel / cross / ESC)
        this.resetPendingData();
        this.spinner.hide();
        this.jobroster.sendFilterData();
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

    // Close modal
    this.modalRef?.close();
    this.resetPendingData();
  }


  getBoxDate() {
    this.selectedSiteBox = this.site;
    this.selectedValue = '';
    this.currentDay = this.day;
    const form = this.addShiftBasicForm;
    if (this.calenderType === 'location') {
      form.get('site_name').setValue(this.site.site_name);
      form.get('site_id').setValue(this.site.id);
      if (this.site.customer_id) {
        this.getCusGuard(this.site.id);
      }
    } else if (this.calenderType === 'staff') {
      this.guard_id = this.site.id;
      form.get('guard_name').setValue(this.site.site_name);

      form.get('guard_id').setValue(this.site.id);
      this.getGuardSite(this.site.id);
    }

    this.clickedboxDate = moment(this.day.customFormat, 'ddd , DD/MM').format('DD/MM/YYYY');
    this.clickedboxDate1 = moment(this.day, 'ddd , DD/MM').format('ddd , DD MMM');

    this.addShiftBasicForm.patchValue({
      start_time: '00:00',
      end_time: '00:00',
    });
  }

  guardList: any = []
  getCusGuard(id) {
    this.rosterService.getGuardBySite(id, this.day).subscribe(res => {
      if (res.success) {
        this.guards = res.data
        if (res.data) {
          res.data.forEach(element => {
            element.name = element.first_name + (element.middle_name ? ' ' + element.middle_name : '') + (element.last_name ? ' ' + element.last_name : '');
          });
        }
        this.guardList = res.data;
        this.filteredList = res.data;
      }
      this.isPublicHoliday = res?.is_holiday
      this.cdr.markForCheck()
    }, (error => {
      this.toast.toastNotification1('There is something went wrong on fetching staff. Please contact with support team', 'Request Incomplete!')
    }))
  }

  getGuardSite(id) {
    this.rosterService.getGuardSite(id, this.day).subscribe(res => {
      if (res.success) {
        this.guards = res.data
        this.filteredList = res.data;
      }
      this.isPublicHoliday = res?.is_holiday
      this.cdr.markForCheck()
    }, (error => {
      this.toast.toastNotification1('There is something went wrong on fetching locations. Please contact with support team', 'Request Incomplete!')
    }))
  }


  filterDropdown(e) {
    if (typeof e !== 'string') {
      return;
    }
    window.scrollTo(window.scrollX, window.scrollY + 1);
    let searchString = e.toLowerCase();
    if (!searchString) {
      this.filteredList = this.guards.slice();
      return;
    } else {
      if (this.calenderType == 'staff') {
        this.filteredList = this.guards.filter(
          user => user.site_name.toLowerCase().indexOf(searchString) > -1
        );

      }
      else {
        this.filteredList = this.guards.filter(
          user => user.first_name.toLowerCase().indexOf(searchString) > -1 ||
            user.last_name.toLowerCase().indexOf(searchString) > -1
        );

      }
    }
    window.scrollTo(window.scrollX, window.scrollY - 1);
  }

  selectValue(name) {
    this.selectedStaffName = name.first_name + " " + name.last_name
    this.selectedStaffPic = name.profile_image
    this.selectedValue = name;
    // if(this.selectedValue){
    //   this.guardsAvailability(this.selectedValue.id)
    // }
    if (this.calenderType === 'staff') {
      this.addShiftBasicForm.get('site_id').setValue(this.selectedValue.site_id)
    }
    else if (this.calenderType === 'location') {
      this.addShiftBasicForm.get('guard_id').setValue(this.selectedValue.id)
    }

  }

  openVerticallyCentered(p) {
    // this.isShowShiftTask = true
    // while (this.job_roster_tasks.length !== 0) {
    //   this.job_roster_tasks.removeAt(0);
    // }
    // this.modalService.open(content, { centered: true, size: 'lg', windowClass: 'modelZ-index', animation: true }).result.then(
    //   (result) => {
    //     this.closeResult = `Closed with: ${result}`;
    //   },
    //   (reason) => {
    //     this.closeResult = `Dismissed ${this.getDismissReason(reason)}`;
    //   },
    // );
    // this.popover = true
    const modalRef = this.modalService.open(AdvanceShiftComponent, {
      size: 'lg',
      centered: true,
      backdropClass: 'modal-no-backdrop',
      backdrop: 'static',
      windowClass: 'modelZ-index',
      animation: true,
    });
    modalRef.componentInstance.calenderType = this.calenderType;
    modalRef.componentInstance.site = { site_name: this.selectedSiteBox.site_name, id: this.selectedSiteBox.id, customer_id: this.selectedSiteBox?.customer_id };
    modalRef.componentInstance.currentDay = this.currentDay;
    modalRef.componentInstance.permissions = this.rosterPermissions;
    modalRef.componentInstance.jobRosterId = this.jobRosterId;
    modalRef.result.then(
      (result) => {
        if (result == true) {
          this.jobroster.sendFilterData()
        }
      },
      (reason) => {
        console.log(`Dismissed with reason: ${this.getDismissReason(reason)}`);
      }
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


  onChangeHour(type, event) {
    // this.selectedValue=''
    // this.addShiftBasicForm.get('guard_id').setValue('')
    const start = this.addShiftBasicForm.get('start_time').value;
    const end = this.addShiftBasicForm.get('end_time').value;
    this.shiftLength = this.commonService.shiftLength(start, end)
  }

  // guardsAvailability(id:number){
  //   let object = this.global.AddTimeDate(this.addShiftBasicForm.value.start_time, this.addShiftBasicForm.value.end_time, this.currentDay?.momentFormat)
  //   if (object.status && object.status == 'null') {
  //     object.status = 'Shift Operation!'
  //     this.toast.toastNotification1(object.msg, object.status)
  //   }
  //   else{
  //     let data = {
  //       start:object.start,
  //       guard_id:id
  //     }
  //     this.rosterService.checkGuardsGvailibilty(data).subscribe((res) => {
  //       console.log(res);
        
  //     })
  //   }
  // }

  isPartTime:boolean = false;
  isExceeded:boolean = false;

  guardsAvailability(id: number) {
    this.isExceeded = false;
    this.isPartTime =false;
    let object = this.global.AddTimeDate(this.addShiftBasicForm.value.start_time, this.addShiftBasicForm.value.end_time, this.currentDay?.momentFormat);
    if (object.status && object.status == 'null') {
        // object.status = 'Shift Operation!';
        // this.toast.toastNotification1(object.msg, object.status);
    } else {
        let data = {
            guard_id: id,
            start: object.start,
            end:object.end,
            
        };
        this.rosterService.checkGuardsGvailibilty(data).subscribe((res) => {
            if (!res.success) {
                console.log(res);
                if(res.is_part_timer){
                  this.isPartTime = true;
                  console.log('isparttime: ', this.isPartTime);
                  
                this.openPartTimeGuardDialog(res?.message);

                }
                else if(res.exceeded ){
                  this.isExceeded = true;
                  console.log('isexceeded; ', this.isExceeded);
                this.openPartTimeGuardDialog(res?.message);

                  
                }
              
            } else {
                console.log(res);
            }
        });
    }

    
  }
  openPartTimeGuardDialog(message: string): void {
    this.modalMessage = message
    const modalRef: NgbModalRef = this.modalService.open(this.partTimeGuardDialog, { windowClass: 'center-modal' });
    

    modalRef.result.then((result) => {
      if (result === 'continue') {
        console.log('User chose to continue');
      } else if (result === 'discard') {
        console.log('User chose to discard');
      }
    }, (reason) => {
      console.log(`Dismissed: ${reason}`);
    });
  }

  showFullTimer(event: Event,modalRef: NgbModalRef) {
    let id = this.addShiftBasicForm.get('site_id').value
    let object = this.global.AddTimeDate(this.addShiftBasicForm.value.start_time, this.addShiftBasicForm.value.end_time, this.currentDay?.momentFormat);
    if (object.status && object.status == 'null') {
        object.status = 'Shift Operation!';
        this.toast.toastNotification1(object.msg, object.status);
    } else {
        let data = {
            site_id: id,
            date:this.clickedboxDate = moment(this.day.customFormat, 'ddd , DD/MM').format('DD-MM-YYYY'),
            // end:object.end,
            
        };
    this.rosterService.fullTimerGuard(data).subscribe((res)=>{
      console.log(res);
      if(res.success){
        this.filteredList = res.data;
        this.selectedValue =''
        this.toast.toastNotification('Guard list has updated with full timer Gaurds.','List Updated')
        modalRef.close('continue');
      }
      
    })
    // modalRef.close('continue');
  }
}

  onDiscard(event: Event, modalRef: NgbModalRef, p) {
    modalRef.close('discard');
  }
  onContinue(event: Event, modalRef: NgbModalRef) {
    modalRef.close('discard');
  }

  
 
}
