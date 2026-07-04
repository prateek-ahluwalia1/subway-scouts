import { Component, Input, OnInit } from '@angular/core';
import { NgbModalRef } from '@ng-bootstrap/ng-bootstrap';
import { JobRoster1Service } from 'app/services/job-roster1.service';
import { GlobalVariable } from 'app/shared/global';
import { NgxSpinnerService } from 'ngx-spinner';

@Component({
  selector: 'app-check-available-staff',
  templateUrl: './check-available-staff.component.html',
  styleUrls: ['./check-available-staff.component.scss']
})
export class CheckAvailableStaffComponent implements OnInit {

  @Input() fromParent;
  @Input() date;
  staff: any[] = []
  modalRef: NgbModalRef;

  constructor(private service: JobRoster1Service, private spinner: NgxSpinnerService,
    private global: GlobalVariable) { }

  ngOnInit(): void {
    console.log(this.date);
    
    if (this.fromParent?.value?.site_id) {
      this.checkStaffAvailbility(true)
    }
    else {
      this.checkStaffAvailbility(false)
    }

  }

  close(data) {
    this.modalRef.dismiss(data)
  }

  checkStaffAvailbility(status) {
    let object = this.global.AddTimeDate(this.fromParent?.value?.start_time, this.fromParent?.value?.end_time, this.date?.momentFormat)
    let newObject = {
      start: object.start,
      end: object.end,
      siteId: this.fromParent?.value?.site_id
    }
    this.spinner.show()
    this.service.checkStaffAvailbility(status, newObject).subscribe(({ guards, data }) => {
      if (status) {
        this.staff = guards
      }
      else {
        this.staff = data
      }
      this.spinner.hide()
    },
      (() => {
        this.spinner.hide()
      }))
  }

  setDefaultImage(event) {
    event.target.src = 'assets/images/outlook-profile.png'; // Replace with your default image path
  }

  addStaff(staff) {
    this.close(staff)
  }

  isEmptyObject(obj) {
    return Object.keys(obj).length === 0;
}

}
