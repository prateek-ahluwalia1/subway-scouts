import { Component, Input, OnInit } from '@angular/core';
import { AbstractControl, FormGroup } from '@angular/forms';
import { FormServiceOneTwoService } from 'app/services/form-service-one-two.service';
import { GlobalVariable } from 'app/shared/global';
import moment from 'moment';
import { ToastServiceService } from 'app/services/toast-service.service';
import { ModalDismissReasons, NgbModal } from '@ng-bootstrap/ng-bootstrap';
import { RunsheetAdvanceShiftComponent } from '../runsheet-advance-shift/runsheet-advance-shift.component';
import { RunsheetJobRosterComponent } from '../runsheet-job-roster.component';
import { RunsheetrosterService } from 'app/services/runsheetroster.service';
import { NgxSpinnerService } from 'ngx-spinner';
import { AppearanceAnimation, ButtonLayoutDisplay, ButtonMaker, ConfirmBoxInitializer, DialogLayoutDisplay, DisappearanceAnimation } from '@costlydeveloper/ngx-awesome-popup';
import { CommonOneTwoService } from '../../operations-modules/components/job-roster/common-one-two.service';

@Component({
  selector: 'app-runsheet-basic-shift',
  templateUrl: './runsheet-basic-shift.component.html',
  styleUrls: ['./runsheet-basic-shift.component.scss']
})
export class RunsheetBasicShiftComponent implements OnInit {

  selectedSiteBox;
  @Input() site: any;
  public selectedValue: any;
  currentDay;
  @Input() day: any;
  @Input() calenderType: string;
  addShiftBasicForm: FormGroup;
  clickedboxDate: string;
  clickedboxDate1: string;
  guard_id: any;
  selectedStaffName: string = 'N/A';
  selectedStaffPic: any;
  shiftLength: string;
  public filteredList: any = [];
  guards: any = [];

  constructor(private formField: FormServiceOneTwoService, private global: GlobalVariable, private commonService: CommonOneTwoService,
                private toast: ToastServiceService, private modalService: NgbModal, private runsheet_jobroster: RunsheetJobRosterComponent, 
                private roster: RunsheetrosterService, private spinner: NgxSpinnerService,) { }

  ngOnInit(): void {

    this.addShiftBasicForm = this.formField.initializeRunsheetShiftBasicForm();
    this.filteredList = this.guards;
  }

  get f(): { [key: string]: AbstractControl } {
    return this.addShiftBasicForm.controls;
  }

  getBoxDate() {
    this.selectedSiteBox = this.site
    this.selectedValue = ''
    this.currentDay = this.day
    if (this.calenderType == 'run_sheet') {
      this.addShiftBasicForm.get('site_name').setValue(this.site.run_sheet_title)
      this.addShiftBasicForm.get('run_sheet_id').setValue(this.site.run_sheet_id)
    }
    else if (this.calenderType == 'guard') {
      this.guard_id = this.site.guard_id
      this.addShiftBasicForm.get('guard_name').setValue(`${this.site.first_name} ${this.site.middle_name ? this.site.middle_name + ' ' : ''}${this.site.last_name}`)
      this.addShiftBasicForm.get('guard_id').setValue(this.site.guard_id)
    }

    this.clickedboxDate = moment(this.day.customFormat, "ddd , DD/MM").format('DD/MM/YYYY')
    this.clickedboxDate1 = moment(this.day, "ddd , DD/MM").format('ddd , DD MMM')
    if (this.site.customer_id && this.calenderType == 'run_sheet') {
      this.getCusGuard(this.site.run_sheet_id)
    }
    else if (this.site.guard_id && this.calenderType == 'guard') {
      this.getGuardSite(this.site.guard_id)
    }
  }

  onChangeHour(type, event) {
    const start = this.addShiftBasicForm.get('start_time').value;
    const end = this.addShiftBasicForm.get('end_time').value;

    if (start && end) {
      const startTime = moment(start, 'HH:mm');
      const endTime = moment(end, 'HH:mm');
      if (endTime.isBefore(startTime)) {
        endTime.add(1, 'day');
      }
      const durationMinutes = endTime.diff(startTime, 'minutes');
      const durationHours = Math.floor(durationMinutes / 60);
      const durationMinutesRemainder = durationMinutes % 60;
      this.shiftLength = `${durationHours}.${durationMinutesRemainder < 10 ? '0' : ''}${durationMinutesRemainder}`;
    }
  }

  selectValue(name) {
    this.selectedStaffName = name.first_name + " " + name.last_name
    this.selectedStaffPic = name.profile_image
    this.selectedValue = name;
    if (this.calenderType == 'guard') {
      this.addShiftBasicForm.get('run_sheet_id').setValue(this.selectedValue.id)
    }
    else if (this.calenderType == 'run_sheet') {
      this.addShiftBasicForm.get('guard_id').setValue(this.selectedValue.id)
    }

  }

  submitted: boolean = false
  onSubmit(p) {
    const isAdmin = this.global.admin.admin_user_type === 'super-admin';
    const max14Hours = false;
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
    if (this.submitted) {
      p.close()
      if (this.calenderType == 'guard') {
        this.addShiftBasicForm.get('guard_id').setValue(this.guard_id)
      }
      this.addShiftBasicForm.value.publish_status = 0
      this.addShiftBasicForm.value.admin_id = this.global.admin.admin_id
      this.saveRoster(this.addShiftBasicForm.value)
    }
  }

  saveRoster(value): void {
    this.spinner.show();
    const isAdmin = this.global.admin.admin_id;
    const rosterId = localStorage.getItem('runsheet_rosterId');
    value.admin = isAdmin;
    value.run_sheet_roster_id = rosterId;
    this.roster[('addShift')](value).subscribe(res => {
      const status = 'Job Roster Operation';
      this.handleRosterResponse(res, status, value);
    }, (error) => {
      this.spinner.hide();
      this.toast.toastNotification1('Something went wrong. Please contact the support team.', 'Request Incomplete');
    });
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
        this.runsheet_jobroster.sendFilterData();
      }
    });
  }

  handleRosterResponse(res, status, value): void {
    if (res.success) {
      this.toast.toastNotification(res.message, status);
      this.formField.initializeAddShiftBasicForm().reset();
      this.addShiftBasicForm.get('start_time').setValue('00:00');
      this.addShiftBasicForm.get('end_time').setValue('00:00');
      this.selectedValue = '';
      this.runsheet_jobroster.sendFilterData();
      this.submitted = false;
      this.global.timestamp = Date.now();
    } else {
      this.addWithConflict(res, value, 'saveRoster');
    }

    this.global.timestamp = Date.now();
    this.spinner.hide();
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
      if (this.calenderType == 'guard') {
        this.filteredList = this.guards.filter(
          user => user.title.toLowerCase().indexOf(searchString) > -1
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

  openVerticallyCentered(p) {
    const modalRef = this.modalService.open(RunsheetAdvanceShiftComponent, {
      size: 'lg',
      centered: true,
      backdropClass: 'modal-no-backdrop',
      backdrop: 'static',
      windowClass: 'modelZ-index',
      animation: true,
    });
    if(this.calenderType === 'run_sheet'){
      modalRef.componentInstance.calenderType = this.calenderType;
      modalRef.componentInstance.site = { site_name: this.selectedSiteBox.run_sheet_title, id: this.selectedSiteBox.run_sheet_id, customer_id: this.selectedSiteBox?.customer_id };
      modalRef.componentInstance.currentDay = this.currentDay;
    }
    else{
      modalRef.componentInstance.calenderType = this.calenderType;
      modalRef.componentInstance.site = { site_name: this.selectedSiteBox.first_name, id: this.selectedSiteBox.guard_id, customer_id: this.selectedSiteBox?.customer_id };
      modalRef.componentInstance.currentDay = this.currentDay;
      // console.log("Submit guard data")
    }
    modalRef.result.then(
      (result) => {
        if (result == true) {
          this.runsheet_jobroster.sendFilterData()
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

  guardList: any = []
  getCusGuard(id) {
    this.roster.getGuardBySite(id).subscribe(res => {
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
    }, (error => {
      this.toast.toastNotification1('There is something went wrong on fetching staff. Please contact with support team', 'Request Incomplete!')
    }))
  }

  getGuardSite(id) {
    this.roster.getGuardSite(id).subscribe(res => {
      if (res.success) {
        this.guards = res.data
        this.filteredList = res.data;
      }
    }, (error => {
      this.toast.toastNotification1('There is something went wrong on fetching locations. Please contact with support team', 'Request Incomplete!')
    }))
  }

}
