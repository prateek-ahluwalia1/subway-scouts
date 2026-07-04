import { Component, Input, OnInit } from '@angular/core';
import { ThemePalette } from '@angular/material/core';
import { ModalDismissReasons, NgbActiveModal, NgbModal, NgbModalRef } from '@ng-bootstrap/ng-bootstrap';
import { GlobalVariable } from 'app/shared/global';
import moment from 'moment';
import { CopyDaySiteComponent } from '../copy-day-site/copy-day-site.component';
import { NgxSpinnerService } from 'ngx-spinner';

export interface Task {
  completed: boolean;
  subtasks: { weekNum: number, date: string, completed: boolean; }[];
}
@Component({
  selector: 'app-copy-shift-bulk',
  templateUrl: './copy-shift-bulk.component.html',
  styleUrls: ['./copy-shift-bulk.component.scss']
})
export class CopyShiftBulkComponent implements OnInit {


  @Input() fromRunsheet;
  @Input() customer_id: any[] = [];
  includeAllNotes: boolean = false;
  includeAllRates: boolean = false;
  removeStaff: boolean = false;
  task: Task = {
    completed: false,
    subtasks: [
    ],
  };

  shiftCopyFrom
  shiftCopyTo
  weekRanges: string[] = [];

  constructor(public activeModal: NgbActiveModal, private global: GlobalVariable, private modalService: NgbModal,
    private spinner: NgxSpinnerService) {
      this.getWeekDays();
  }

  selectedDates: any = []

  ngOnInit(): void {
    console.log("Get id", this.customer_id)
  }

  allComplete: boolean = false;
  updateAllComplete(subtask) {
    if (subtask.completed) {
      this.selectedDates.push(subtask);
    } else {
      const index = this.selectedDates.findIndex(item => item === subtask);
      if (index !== -1) {
        this.selectedDates.splice(index, 1);
      }
    }

    this.allComplete = this.task.subtasks != null && this.task.subtasks.every(t => t.completed);
  }

  someComplete(): boolean {
    if (this.task.subtasks == null) {
      return false;
    }
    return this.task.subtasks.filter(t => t.completed).length > 0 && !this.allComplete;
  }

  setAll(completed: boolean) {
    this.allComplete = completed;
    if (this.task.subtasks == null) {
      return;
    }
    this.task.subtasks.forEach(t => (t.completed = completed));
    this.task.subtasks.forEach(subtask => {
      subtask.completed = completed;
    });
    this.selectedDates = completed ? [...this.task.subtasks] : [];
  }

  close(data?) {
    this.activeModal.close(data)
  }



  lastDate;
  /////get the week from next to onward
  getWeekDays() {

    const startDate = moment(this.global.start).startOf('isoWeek').add(1, 'week');
    this.shiftCopyFrom = startDate.format('MMM DD');
    const endDate = moment(this.global.start).add(2, 'months').endOf('month');

    // Define a function to get the week range for a given date
    function getWeekRange(date) {
      const startOfWeek = moment(date).startOf('isoWeek');
      const endOfWeek = moment(startOfWeek).add(6, 'days');
      console.log("end", endOfWeek);
      return `${startOfWeek.format('MMM DD')} - ${endOfWeek.format('MMM DD')}`;
    }
    let weekNum = 1;

    // Loop through the dates and get the week ranges
    let currentDate = moment(startDate);
    while (currentDate <= endDate) {
      const weekRange = getWeekRange(currentDate);
      this.task.subtasks.push({ weekNum: weekNum++, date: weekRange, completed: false, });
      currentDate.add(1, 'week');
      this.lastDate = currentDate;
    }
    this.shiftCopyTo = this.lastDate.format('MMM DD');
  }
  copyDayandSite() {
    const modalRef = this.modalService.open(CopyDaySiteComponent, { animation: true, centered: true, windowClass: 'copydayandsite' });
    let data = {
      rates: this.includeAllNotes,
      notes: this.includeAllNotes,
      dates: this.selectedDates,
      remove_staff:this.removeStaff,
      customer_id: this.customer_id
    }
    modalRef.componentInstance.fromParent = data;
    modalRef.componentInstance.from_runsheet = this.fromRunsheet;
    modalRef.result.then(
      (result) => {
        if (result) {
          this.close('success')
        }
      },
      (reason) => {
       
        console.log("Modal Closed Reason -> ", `Dismissed ${this.getDismissReason(reason)}`)
      }
    );
  }

  getDismissReason(reason: any): string {
    if (reason === ModalDismissReasons.ESC) {
      return "by pressing ESC";
    } else if (reason === ModalDismissReasons.BACKDROP_CLICK) {
      return "by clicking on a backdrop";
    } else {
      return `${reason}`;
    }
  }
}
