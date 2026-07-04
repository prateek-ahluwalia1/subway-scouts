import { NgbActiveModal } from '@ng-bootstrap/ng-bootstrap';
import { Component, OnInit, ViewChild } from '@angular/core';
import { MatDialogRef } from '@angular/material/dialog';
import { MAT_DIALOG_DATA } from '@angular/material/dialog';
import { Inject } from '@angular/core';

@Component({
  selector: 'app-dialogbox',
  templateUrl: './dialogbox.component.html',
  styleUrls: ['./dialogbox.component.scss']
})
export class DialogboxComponent implements OnInit {
  @ViewChild('rootSVG') rootSVG;
  constructor(public dialogRef: MatDialogRef<DialogboxComponent>, @Inject(MAT_DIALOG_DATA) public data: any) {
    console.log('data is ', data);

  }

  ngOnInit(): void {
  }

  // ngAfterViewInit() {

  //   setInterval(()=>{
  //     this.rootSVG.nativeElement.pauseAnimations();
  //   }, 1000)

  //   setInterval(()=>{
  //     this.rootSVG.nativeElement.unpauseAnimations();
  //   }, 9500)


  // }
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
