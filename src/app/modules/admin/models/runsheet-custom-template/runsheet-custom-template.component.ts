import { Component, Input, OnInit } from '@angular/core';
import { FormArray, FormBuilder, FormGroup, Validators } from '@angular/forms';
import { faCalendar, faClock, faLocationPinLock, faTrash, faXmark } from '@fortawesome/free-solid-svg-icons';
import { NgbActiveModal, NgbNavChangeEvent } from '@ng-bootstrap/ng-bootstrap';
import { RunsheetrosterService } from 'app/services/runsheetroster.service';
import { ToastServiceService } from 'app/services/toast-service.service';

@Component({
  selector: 'app-runsheet-custom-template',
  templateUrl: './runsheet-custom-template.component.html',
  styleUrls: ['./runsheet-custom-template.component.scss']
})
export class RunsheetCustomTemplateComponent implements OnInit {

  faXmark = faXmark;
  faClock = faClock;
  faMapLocation = faLocationPinLock;
  faTrash = faTrash;
  calenderIcon = faCalendar;
  active;
  disabled = true;
  addCustomTemp: FormGroup
  @Input() fromParent
  onNavChange(changeEvent: NgbNavChangeEvent) {
    if (changeEvent.nextId === 3) {
      changeEvent.preventDefault();
    }
  }

  constructor(private activeModal: NgbActiveModal, private fb: FormBuilder,
    private roster: RunsheetrosterService, private toast: ToastServiceService) { }

  ngOnInit(): void {
    this.addCustomTemp = this.fb.group({
      start: ['', Validators.required],
      end: ['', Validators.required],
      shift_type: ['template'],
      run_sheet_job_roster_tasks: this.fb.array([
      ]),
    });
  }

  textAreasList: any = [];

  get run_sheet_job_roster_tasks(): FormArray {
    return this.addCustomTemp.get('run_sheet_job_roster_tasks') as FormArray;
  }

  addTask() {
    this.run_sheet_job_roster_tasks.push(this.fb.group({
      task_start: [''],
      task_end: [''],
      task: [''],
    }));
    console.log(this.addCustomTemp.value.task)
  }

  removeTextArea(index) {
    (this.addCustomTemp.get('run_sheet_job_roster_tasks') as FormArray).removeAt(index);
  }

  close(data?) {
    this.activeModal.close(data);
  }

  submitted = false;
  onFormSubmit(value) {
    this.submitted = true;
    if (this.addCustomTemp.invalid) {
      return;
    }
    // if (this.addCustomTemp.value.run_sheet_job_roster_tasks) {
    //   this.addCustomTemp.value.run_sheet_job_roster_tasks.forEach(element => {
    //     this.getStartEnd(element.task_start)
    //   });
    // }
    if (this.addCustomTemp.value.start == this.addCustomTemp.value.end) {
      this.toast.toastNotification1('Start and end time should not be equal', 'Time Conflict')
      return
    }
    value.run_sheet_job_roster_id = localStorage.getItem('runsheet_rosterId')
    this.roster.addShift(value).subscribe(res => {
      let status = 'Job Roster Operation!'
      if (res.success) {
        this.toast.toastNotification(res.message, status)
        this.addCustomTemp.reset()
        this.close('addTemplate')
      }
    }, (error => {

    }))


  }

}
