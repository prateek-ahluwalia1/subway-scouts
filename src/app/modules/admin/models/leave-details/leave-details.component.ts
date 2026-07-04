import { Component, Input, OnInit } from '@angular/core';
import { FormControl, FormGroup, Validators } from '@angular/forms';
import { DateAdapter } from '@angular/material/core';
import { NgbActiveModal } from '@ng-bootstrap/ng-bootstrap';
import { StaffService } from 'app/services/staff.service';
import { ToastServiceService } from 'app/services/toast-service.service';
import { GlobalVariable } from 'app/shared/global';
import moment from 'moment';

@Component({
  selector: 'app-leave-details',
  templateUrl: './leave-details.component.html',
  styleUrls: ['./leave-details.component.scss']
})
export class LeaveDetailsComponent implements OnInit {
  guardList: any = []
  leaveFromAdmin: any = []
  sideType = 'admin'
  leaveRequest: any = []
  leave_type
  active = false;
  @Input() type;
  @Input() fromParent;
  range = new FormGroup({
    start: new FormControl<Date | null>(null, Validators.required),
    end: new FormControl<Date | null>(null, Validators.required),
  });
  leaveTypeControl = new FormControl('sick', Validators.required);
  guardIdControl = new FormControl(null, Validators.required);
  minDate: Date;
  constructor(private ngbActiveModal: NgbActiveModal, public dateAdapter: DateAdapter<Date>,
    private global: GlobalVariable, private service: StaffService, private toast: ToastServiceService) {
    this.dateAdapter.setLocale('en-AU');
    this.minDate = new Date();
  }

  ngOnInit(): void {

    if (this.type == 'add') {
      this.getGuards()
    }
    if (this.fromParent) {
      this.getLeaveRequest()
    }

  }
  close(data) {
    this.ngbActiveModal.dismiss(data);
  }
  change(type) {
    this.sideType = type
  }

  submitLeave() {

    if (this.range.valid && this.guardIndex && this.guardIndex.value) {
      let data = {
        admin_id: this.global.admin.admin_id,
        reason: this.leave_type,
        date: moment(this.range.value.start).format('MM-DD-YYYY') + " - " + moment(this.range.value.end).format('MM-DD-YYYY'),
        guard_id: this.guardIndex.value.id
      }
      this.service.addLeave(data).subscribe(({ success, message }) => {
        if (success) {
          this.service.getstaffLeave()
          this.close('added')
          this.toast.toastNotification(message, 'Guard Leave!')
        }
      })
      // console.log("Perfect")
    }
    else {
      this.toast.toastNotification1("Please ensure all fields are filled out. If you don't select a leave type, 'Sick' will be stored by default.", 'Error')
    }

  }


  guardIndex
  receiveDataFromChildGuardsSingle(data: string) {
    this.guardIndex = data
  }

  getLeaveRequest() {
    let data = {
      id: this.fromParent.id,
      days: this.fromParent.days
    }
    this.service.getLeaveRequest(data).subscribe(({ success, data, admin_leaves }) => {
      if (success) {
        this.leaveRequest = data
        this.leaveFromAdmin = admin_leaves
      }
    })
  }

  getGuards() {
    this.service.getGuard().subscribe(({ success, data }) => {
      if (success) {
        if (data) {
          data.forEach(element => {
            element.name = element.first_name + (element.middle_name ? ' ' + element.middle_name : '') + (element.last_name ? ' ' + element.last_name : '');
          });
        }
        this.guardList = data;
      }
    })
  }


  approveLeave(value) {
    console.log(value);
    let data = {
      id: value.id
    }
    this.service.approveLeave(data).subscribe(({ success, message }) => {
      if (success) {
        this.getLeaveRequest()
        this.toast.toastNotification(message, 'Leave Requst!')
      }
    })
  }
}
