import { Component, OnInit, Inject, Input } from '@angular/core';
import { NgbActiveModal } from '@ng-bootstrap/ng-bootstrap';
import { ToastServiceService } from 'app/services/toast-service.service';
import moment from 'moment';
import { GlobalVariable } from 'app/shared/global';
import { CommonOneTwoService } from '../../operations-modules/components/job-roster/common-one-two.service';
@Component({
  selector: 'app-update-time',
  templateUrl: './update-time.component.html',
  styleUrls: ['./update-time.component.scss']
})
export class UpdateTimeComponent implements OnInit {

  type = 'start'
  start_time
  end_time
  @Input() start;
  @Input() end;
  @Input() currentDay
  @Input() rosterPermissions
  @Input() runsheet_roster
  constructor(public modalService: NgbActiveModal, private global: GlobalVariable,
    private toast: ToastServiceService, private commonService: CommonOneTwoService) {

  }
  onCancel(): void {
    this.modalService.close('dismiss');
  }
  ngOnInit(): void {
    this.start_time = moment(this.start, 'DD-MM-YYYY HH:mm').format('HH:mm');
    this.end_time = moment(this.end, 'DD-MM-YYYY HH:mm').format('HH:mm');
  }
  onSubmit() {
    if(this.runsheet_roster === 'run_sheet'){
      if (this.start_time && this.end_time) {
        const shiftLengths = this.commonService.shiftLength(this.start_time, this.end_time)
        const isAdmin = this.global.admin.admin_user_type === 'super-admin';
        const max14Hours = false;
        const shiftLength = parseFloat(shiftLengths).toFixed(2);
        const results = this.commonService.checkShiftLengthAccess(isAdmin, max14Hours, shiftLength)
        if (!results) {
          return
        }
      }
    }
    else{
      if (this.start_time && this.end_time) {
        const shiftLengths = this.commonService.shiftLength(this.start_time, this.end_time)
        const isAdmin = this.global.admin.admin_user_type === 'super-admin';
        const max14Hours = this.rosterPermissions?.max14Hours || false;
        const shiftLength = parseFloat(shiftLengths).toFixed(2);
        const results = this.commonService.checkShiftLengthAccess(isAdmin, max14Hours, shiftLength)
        if (!results) {
          return
        }
      }
    }
    this.getStartEnd(this.start_time, this.end_time)
    this.modalService.close({ start: this.start, end: this.end });
  }

  //Time chack if end is greater than start
  getStartEnd(start, end) {
    let newStart = moment(start, "HH:mm")
    let newEnd = moment(end, "HH:mm")
    if (newStart.isSame(newEnd)) {
      let status = 'Shift Operation'
      let msg = 'Start and End time can not be equal'
      this.toast.toastNotification1(msg, status)
    }
    const finalStart = moment(this.currentDay).format("MM-DD-YYYY") + " " + start
    this.start = finalStart
    if (newEnd < newStart) {
      newEnd = moment(this.currentDay).startOf('day')
      let endTime = newEnd.add(1, 'day')
      let finalEnd = endTime.format("MM-DD-YYYY") + " " + end;
      this.end = finalEnd
    }
    else {
      const finalEnd = moment(this.currentDay).format("MM-DD-YYYY") + " " + end
      this.end = finalEnd
    }
  }


}
