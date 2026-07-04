import { Component, Input, OnInit } from '@angular/core';
import { faListCheck } from '@fortawesome/free-solid-svg-icons';
import { NgbActiveModal } from '@ng-bootstrap/ng-bootstrap';
import { StaffService } from 'app/services/staff.service';
import { ToastServiceService } from 'app/services/toast-service.service';
import { GlobalVariable } from 'app/shared/global';

@Component({
  selector: 'app-add-unavailability',
  templateUrl: './add-unavailability.component.html',
  styleUrls: ['./add-unavailability.component.scss']
})
export class AddUnavailabilityComponent implements OnInit {
  availability_days = [
    {
      id: 1, day: 'Monday', start: 'Start', end: 'End', toggleValue: false, startTime: '00:00', endTime: '00:00', types: [
        { value: 'full time', viewValue: 'Full Time' },
        { value: 'others', viewValue: 'Others' },
      ], data: '', showJobLevel: false, showTimePicker: false
    },
    {
      id: 2, day: 'Tuesday', start: 'Start', end: 'End', toggleValue: false, startTime: '', endTime: '', types: [
        { value: 'full time', viewValue: 'Full Time' },
        { value: 'others', viewValue: 'Others' },
      ], data: '', showJobLevel: false, showTimePicker: false
    },
    {
      id: 3, day: 'Wednesday', start: 'Start', end: 'End', toggleValue: false, startTime: '', endTime: '', types: [
        { value: 'full time', viewValue: 'Full Time' },
        { value: 'others', viewValue: 'Others' },
      ], data: '', showJobLevel: false, showTimePicker: false
    },
    {
      id: 4, day: 'Thursday', start: 'Start', end: 'End', toggleValue: false, startTime: '', endTime: '', types: [
        { value: 'full time', viewValue: 'Full Time' },
        { value: 'others', viewValue: 'Others' },
      ], data: '', showJobLevel: false, showTimePicker: false
    },
    {
      id: 5, day: 'Friday', start: 'Start', end: 'End', toggleValue: false, startTime: '', endTime: '', types: [
        { value: 'full time', viewValue: 'Full Time' },
        { value: 'others', viewValue: 'Others' },
      ], data: '', showJobLevel: false, showTimePicker: false
    },
    {
      id: 6, day: 'Saturday', start: 'Start', end: 'End', toggleValue: false, startTime: '', endTime: '', types: [
        { value: 'full time', viewValue: 'Full Time' },
        { value: 'others', viewValue: 'Others' },
      ], data: '', showJobLevel: false, showTimePicker: false
    },
    {
      id: 7, day: 'Sunday', start: 'Start', end: 'End', toggleValue: false, startTime: '', endTime: '', types: [
        { value: 'full time', viewValue: 'Full Time' },
        { value: 'others', viewValue: 'Others' },
      ], data: '', showJobLevel: false, showTimePicker: false
    }
  ];

  @Input() fromParentUserMenu;
  @Input() data;

  constructor(public ngbActiveModal: NgbActiveModal, private staffService: StaffService,
    private toast: ToastServiceService, private global: GlobalVariable) {
  }

  ngOnInit(): void {
  }

  close(data?) {
    this.ngbActiveModal.dismiss(data);
  }

  choose(type: string, day): void {
    day.data = type;
    day.showTimePicker = type === 'others';
  }

  toggleChange(checked: boolean, day): void {
    day.toggleValue = checked;
    day.showJobLevel = checked;
    day.showTimePicker = checked;
    if (!checked) {
      day.endTime = ''
      day.startTime = ''
      day.data = ''
    }
  }

  submitData() {
    this.availability_days.forEach(item => {
      if (item.data === '' || item.data === null) {
        item.data = 'N/A';
      }
    });
    console.log("form data", this.availability_days);

    let data = {
      submittedAvailability: this.availability_days,
      guard_id: this.fromParentUserMenu,
      admin_id: this.global.admin.admin_id
    }

    this.staffService.addStaffAvailbility(data).subscribe(res => {
      if (res.success) {
        this.close('submitted');
        let status = 'Staff Operation!'
        this.toast.toastNotification(res.message, status)
      }
    })
  }

  onChangeHour(day, event) {
    console.log(event);
    day.startTime = event;
  }

  ngAfterViewInit(): void {
    console.log(this.data);
    if (this.data) {
      this.availability_days.forEach(day => {
        const match = this.data.find(item => item.day === day.day);
        if (match) {
          day.toggleValue = match.toggleValue;
          day.showJobLevel = match.toggleValue;
          day.data = match.data;
          day.startTime = match.startTime;
          day.endTime = match.endTime;
          day.showTimePicker = match.data === 'others'; // Set the flag to true if 'others' is selected
        }
      });
    }
  }
}
