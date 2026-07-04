import { Component, OnInit } from '@angular/core';
import { MatDialogRef } from '@angular/material/dialog';

@Component({
  selector: 'app-staff-fort-night-detail',
  templateUrl: './staff-fort-night-detail.component.html',
  styleUrls: ['./staff-fort-night-detail.component.scss']
})
export class StaffFortNightDetailComponent implements OnInit {
  
  constructor(private dialogRef: MatDialogRef<StaffFortNightDetailComponent>) { }

  ngOnInit(): void {
  }

  close(){
    this.dialogRef.close('')
  }
}
