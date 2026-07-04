import { Component, OnInit } from '@angular/core';
import { MatDialogRef } from '@angular/material/dialog';
import { MAT_DIALOG_DATA } from '@angular/material/dialog';
import { Inject } from '@angular/core';

@Component({
  selector: 'app-account-status',
  templateUrl: './account-status.component.html',
  styleUrls: ['./account-status.component.scss']
})
export class AccountStatusComponent implements OnInit {
  type
  constructor(public dialogRef: MatDialogRef<AccountStatusComponent> ,@Inject(MAT_DIALOG_DATA) public data: any) { }

  ngOnInit(): void {
    this.type = this.data.type
    console.log(this.type);
  }
  okey(){
    this.dialogRef.close();

  }

}
