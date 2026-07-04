import { Component, Input, OnInit, ViewChild } from '@angular/core';
import { GlobalVariable } from 'app/shared/global';
import moment from 'moment';
import { NgbActiveModal } from '@ng-bootstrap/ng-bootstrap';

@Component({
  selector: 'app-staff-activity-details',
  templateUrl: './staff-activity-details.component.html',
  styleUrls: ['./staff-activity-details.component.scss']
})
export class StaffActivityDetailsComponent implements OnInit {


  @Input() fromRoster;
  @Input() total_hours;
  @ViewChild('content') modal: any;
  users = [];
  buit_in_template = [];
  users_data = [];
  employees = []
  visa
  security
  activityWeek: any = []

  constructor(public globals: GlobalVariable, public ngbActiveModal: NgbActiveModal ) {
    // const date = moment(this.globals.getWeekDays[0].customFormat, "ddd , DD/MM");
    // console.log("get global Dates", date)
    // for (var i = 0; i <= 13; i++) {
    //   this.activityWeek.push(moment(date).add(i, 'days').format("ddd , DD/MM"));
    //   console.log("get Dates", this.activityWeek)
    // };
    this.buildActivityWeek();
  }

  ngOnInit(): void {
    if (this.fromRoster && this.fromRoster[0].guard_documents) {
      this.visa = this.fromRoster[0].guard_documents.find((p) => p.document_type === 'visa');
      this.security = this.fromRoster[0].guard_documents.find((p) => p.document_type === 'security_license');
    }
  }

  close(data?) {
    this.ngbActiveModal.dismiss(data);
  }

  // private buildActivityWeek() {
  //   this.activityWeek = [];
  //   const startDate = moment(this.globals.start, "MM-DD-YYYY"); 
  //   if (!startDate.isValid()) {
  //     console.error("Invalid start date:", this.globals.start);
  //     return;
  //   }
  //   for (let i = 0; i < 14; i++) {
  //     const day = startDate.clone().add(i, 'days');
  //     this.activityWeek.push(day.format("ddd , DD/MM"));
  //   }
  //   // console.log("Activity Week built:", this.activityWeek);
  // }

  private readonly FORTNIGHT_ANCHOR = '2025-12-01';

  private buildActivityWeek() {
    this.activityWeek = [];

    const anchor = moment(this.FORTNIGHT_ANCHOR, 'YYYY-MM-DD');
    if (!anchor.isValid()) {
      console.error("Invalid anchor date:", this.FORTNIGHT_ANCHOR);
      return;
    }

    const reference = moment(this.globals.start, "MM-DD-YYYY");
    if (!reference.isValid()) {
      console.error("Invalid globals.start:", this.globals.start);
      return;
    }

    const daysSinceAnchor = reference.diff(anchor, 'days');

    const fullFortnights = Math.floor(daysSinceAnchor / 14);

    const fortnightStart = anchor.clone().add(fullFortnights * 14, 'days');

    for (let i = 0; i < 14; i++) {
      const day = fortnightStart.clone().add(i, 'days');
      this.activityWeek.push(day.format("ddd , DD/MM"));
    }

  }

}
