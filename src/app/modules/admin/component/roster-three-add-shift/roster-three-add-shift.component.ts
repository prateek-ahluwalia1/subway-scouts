import { Component, OnInit } from '@angular/core';
import { NgbActiveModal, NgbModal } from '@ng-bootstrap/ng-bootstrap';
import { GlobalVariable } from 'app/shared/global';
import { NotesForShiftComponent} from '../../models/notes-for-shift/notes-for-shift.component'
import { RosterThreePublishShiftComponent} from '../../component/roster-three-publish-shift/roster-three-publish-shift.component'
import { FormControl, FormGroup } from '@angular/forms';
import {MatDialog} from '@angular/material/dialog';
@Component({
  selector: 'app-roster-three-add-shift',
  templateUrl: './roster-three-add-shift.component.html',
  styleUrls: ['./roster-three-add-shift.component.scss']
})
export class RosterThreeAddShiftComponent implements OnInit {
  isChecked = true;
  Selectdshift: FormGroup
  constructor(public activeModal: NgbActiveModal,
    private model: NgbModal,
    public globals: GlobalVariable,
    public dialog: MatDialog) { }

  ngOnInit(): void {
    this.Selectdshift = new FormGroup({
      start_time: new FormControl(''),
      end_time: new FormControl(''),
    })
  }
  closeModel() {
    this.globals.roster_three_shift_component = false
    this.globals.roster_three_sidebar = true
  }

  editNotesModel(){
    this.model.open(NotesForShiftComponent)
  }

  // get end_time():any{
  //    return this.Selectdshift
  // }

  // showPublish(event){
  //   this.model.open(RosterThreeAddShiftComponent, { windowClass: 'custom-class', animation: true });
  // }
  showPublish(event) {
    this.dialog.open(RosterThreePublishShiftComponent ,{
      width: '500px',
    });
  }

}
