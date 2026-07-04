import { Component, Input, OnInit, TemplateRef, ViewChild } from '@angular/core';
import { NgbActiveModal, NgbModal, NgbModalRef } from '@ng-bootstrap/ng-bootstrap';
import { JobRoster1Service } from 'app/services/job-roster1.service';
import { RunsheetrosterService } from 'app/services/runsheetroster.service';
import { ToastServiceService } from 'app/services/toast-service.service';
import { GlobalVariable } from 'app/shared/global';
import { NgxSpinnerService } from 'ngx-spinner';

export interface Site {
  id: number;
  site_name: string
  selected: boolean;
  sites?: Site[];
}

export interface Task {
  day: number
  name: string;
  completed: boolean;
  subtasks?: Task[];
}

@Component({
  selector: 'app-copy-day-site',
  templateUrl: './copy-day-site.component.html',
  styleUrls: ['./copy-day-site.component.scss']
})
export class CopyDaySiteComponent implements OnInit {

  @Input() fromParent;
  @Input() from_runsheet;
  @ViewChild("showTable") showTable: TemplateRef<any>;
  modalRef: NgbModalRef;
  selectedDates: any = [];
  violations: any = [];
  totalViolations: any;
  suggestion: any;
  selectedShifts: any = []
  task: Task = {
    day: 1,
    name: '',
    completed: false,
    subtasks: [
      { day: 1, name: 'Mon', completed: false },
      { day: 2, name: 'Tue', completed: false },
      { day: 3, name: 'Wed', completed: false },
      { day: 4, name: 'Thurs', completed: false },
      { day: 5, name: 'Fri', completed: false },
      { day: 6, name: 'Sat', completed: false },
      { day: 7, name: 'Sun', completed: false },
    ],
  };

  //select all unselect Site start here

  allChecked: boolean = false;

  site: Site = {
    id: 1,
    site_name: 'Select/Deselect All',
    selected: false,
    sites: [
    ]
  };


  updateAllChecked() {
    this.allChecked = this.site.sites != null && this.site.sites.every(t => t.selected);
  }

  someSelected(): boolean {
    if (this.site.sites == null) {
      return false;
    }
    return this.site.sites.filter(t => t.selected).length > 0 && !this.allChecked;
  }

  setAllSites(selected: boolean) {
    this.allChecked = selected;
    if (this.site.sites == null) {
      return;
    }
    this.site.sites.forEach(t => t.selected = selected);

    this.site.sites.forEach(subtask => {
      subtask.selected = selected;
    });
    this.selectedShifts = selected ? [...this.site.sites] : [];
    console.log(this.selectedShifts);
  }

  updateAllCompleteSites(subtask) {
    if (subtask.selected) {
      this.selectedShifts.push(subtask);
    } else {
      const index = this.selectedShifts.findIndex(item => item === subtask);
      if (index !== -1) {
        this.selectedShifts.splice(index, 1);
      }
    }

    this.allComplete = this.site.sites != null && this.site.sites.every(t => t.selected);
    console.log(this.selectedShifts);

  }

  //code end here for sites

  constructor(public activeModal: NgbActiveModal, private rosterService: JobRoster1Service,
    private global: GlobalVariable, private toast: ToastServiceService,
    private spinner: NgxSpinnerService, private roster: RunsheetrosterService, private modalService: NgbModal,) {

  }

  ngOnInit(): void {

    if(this.from_runsheet === 'run_sheet'){
      this.roster.getSiteOfCurrentWeek().subscribe(({ data, success }) => {
        if (success) {
          data.forEach(element => {
            this.site.sites.push({ id: element.id, site_name: element.title, selected: false });
          });
        }
      })
    }
    else{
      this.rosterService.getCopyShiftsSitesByCustomer(this.fromParent.customer_id).subscribe(({ data, success }) => {
        if (success) {
          data.forEach(element => {
            this.site.sites.push({ id: element.id, site_name: element.site_name, selected: false });
          });
        }
      })
    }
    this.setAll(true)
  }

  close(data?) {
    this.activeModal.close(data)
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
    console.log(this.selectedDates);

  }


  //here we final copied shifts to selected site
  copiedShifts() {
    if(this.from_runsheet === 'run_sheet'){
      const weeks = this.fromParent.dates.map(week => week.weekNum)
      const days = this.selectedDates.map(week => week.day)
      const sites = this.selectedShifts.map(week => week.id)
      let params = {
        weeks: weeks,
        days: days,
        runsheet_id: sites,
        notes: this.fromParent.notes,
        rates: this.fromParent.rates,
        remove_staff: this.fromParent.remove_staff,
        start: this.global.start,
        end: this.global.end,
        admin_id: this.global.admin.admin_id
      }
      console.log("Submit copied data", params)

      this.roster.copiedShiftsToSite(params).subscribe(({ success, message }) => {
        if (success) {
          this.toast.toastNotification(message, 'Shift Copied Operation!')
          this.close(success)
        }
        else {
          this.toast.toastNotification1(message, 'Shift Copied Operation!')
        }
        this.spinner.hide()
      }, error => {
        this.toast.toastNotification1(this.global.apiError, 'Shift Copied Operation!')
        this.spinner.hide()
      })
    }
    else{
      this.spinner.show()
      const weeks = this.fromParent.dates.map(week => week.weekNum)
      const days = this.selectedDates.map(week => week.day)
      const sites = this.selectedShifts.map(week => week.id)
      let params = {
        weeks: weeks,
        days: days,
        sites: sites,
        notes: this.fromParent.notes,
        rates: this.fromParent.rates,
        remove_staff: this.fromParent.remove_staff,
        start: this.global.start,
        end: this.global.end,
        admin_id: this.global.admin.admin_id
      }
      this.rosterService.copiedShiftsToSite(params).subscribe((response) => {
        if (response.success) {
          this.toast.toastNotification(response.message, 'Shift Copied Operation!')
          this.close(response.success)
        }
        else {
          if(response.message == "Work limitation violations detected"){
            this.spinner.hide()
            this.showViolationModal(response);
          }
          else {
            this.toast.toastNotification1(response.message, 'Shift Copied Operation!')
          }
        }
        this.spinner.hide()
      }, error => {
        this.toast.toastNotification1(this.global.apiError, 'Shift Copied Operation!')
        this.spinner.hide()
      })
    }
  }

  private showViolationModal(response: any) {
    this.modalRef = this.modalService.open(this.showTable, {
      centered: true,              
      size: 'lg',                   
      backdrop: 'static',          
    });
    this.violations = response.violations || [];
    this.totalViolations = response.total_violations || 0;
    this.suggestion = response.suggestion || '';
  }

}



