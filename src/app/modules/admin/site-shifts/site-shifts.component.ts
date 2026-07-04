import { Component, OnInit, ViewChild } from '@angular/core';
import { MatDialogRef } from '@angular/material/dialog';
import { MAT_DIALOG_DATA } from '@angular/material/dialog';
import { Inject } from '@angular/core';

@Component({
  selector: 'app-site-shifts',
  templateUrl: './site-shifts.component.html',
  styleUrls: ['./site-shifts.component.scss']
})
export class SiteShiftsComponent implements OnInit {

  constructor(public dialogRef: MatDialogRef<SiteShiftsComponent>, @Inject(MAT_DIALOG_DATA) public data: any) { 
    console.log('data is ', data);
  }

  ngOnInit(): void {
  }

  no(e) {
    this.dialogRef.close(e);

  }
  yes(e) {
    this.dialogRef.close(e);
  }

  okey() {
    this.dialogRef.close();

  }

}
