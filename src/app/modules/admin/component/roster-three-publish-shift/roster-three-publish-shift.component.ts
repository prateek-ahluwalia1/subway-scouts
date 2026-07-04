import { Component, OnInit } from '@angular/core';
import { MatDialogRef } from '@angular/material/dialog';

@Component({
  selector: 'app-roster-three-publish-shift',
  templateUrl: './roster-three-publish-shift.component.html',
  styleUrls: ['./roster-three-publish-shift.component.scss']
})
export class RosterThreePublishShiftComponent implements OnInit {
  email = true;
  mobile_app = true;
  sms = false;
  labelPosition: 'before' | 'after' = 'after';
  disabled = false;
  constructor( public dialogRef: MatDialogRef<RosterThreePublishShiftComponent>) { }

  ngOnInit(): void {
  }

  onCloseClick(): void {
    this.dialogRef.close();
  }
}
