import { Component, Input, OnInit } from '@angular/core';
import { NgbNavChangeEvent, NgbActiveModal } from '@ng-bootstrap/ng-bootstrap';
import { faCalendar, faClock, faLocationPinLock, faTrash, faXmark } from '@fortawesome/free-solid-svg-icons';
import { FormArray, FormBuilder, FormControl, FormGroup, Validators } from '@angular/forms';
import { JobRoster1Service } from 'app/services/job-roster1.service';
import { ToastServiceService } from 'app/services/toast-service.service';
@Component({
  selector: 'app-add-custom-template',
  templateUrl: './add-custom-template.component.html',
  styleUrls: ['./add-custom-template.component.scss']
})
export class AddCustomTemplateComponent implements OnInit {
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

  constructor(
    private activeModal: NgbActiveModal, private fb: FormBuilder,
    private roster: JobRoster1Service, private toast: ToastServiceService
  ) {

  }

  ngOnInit(): void {
    this.addCustomTemp = this.fb.group({
      start: ['', Validators.required],
      end: ['', Validators.required],
      shift_type: ['template'],
      job_roster_tasks: this.fb.array([
      ]),
    });

  }

  textAreasList: any = [];

  // addTextarea(){        
  //     this.textAreasList.push('text_area'+ (this.textAreasList.length + 1));
  // }
  get job_roster_tasks(): FormArray {
    return this.addCustomTemp.get('job_roster_tasks') as FormArray;
  }
  addTask() {
    this.job_roster_tasks.push(this.fb.group({
      task_start: [''],
      task_end: [''],
      task: [''],
    }));
    console.log(this.addCustomTemp.value.task)
  }


  removeTextArea(index) {
    (this.addCustomTemp.get('job_roster_tasks') as FormArray).removeAt(index);
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
    if (this.addCustomTemp.value.job_roster_tasks) {
      this.addCustomTemp.value.job_roster_tasks.forEach(element => {
        this.getStartEnd(element.task_start)
      });
    }
    if (this.addCustomTemp.value.start == this.addCustomTemp.value.end) {
      this.toast.toastNotification1('Start and end time should not be equal', 'Time Conflict')
      return
    }
    value.roster_id = localStorage.getItem('rosterId')
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

  getStartEnd(start) {
    //   this.addCustomTemp.value.job_roster_tasks.forEach((element, index) => {
    //     let newStart = moment(element.task_start, "HH:mm")
    //     let newEnd = moment(element.task_end, "HH:mm")
    //     const finalStart = moment(this.fromParent, "ddd , DD/MM").format("MM-DD-YYYY") + " " + start
    //     this.addCustomTemp.value.job_roster_tasks[index].task_start = finalStart
    //     if (newEnd < newStart) {
    //       newEnd = moment(this.fromParent, "ddd , DD/MM").startOf('day')
    //       let endTime = newEnd.add(1, 'day')
    //       let finalEnd = endTime.format("MM-DD-YYYY") + " " + end;
    //       this.addCustomTemp.value.job_roster_tasks[index].task_end = finalEnd
    //     }
    //     else {
    //       const finalEnd = moment(this.fromParent, "ddd , DD/MM").format("MM-DD-YYYY") + " " + end
    //       this.addCustomTemp.value.job_roster_tasks[index].task_end = finalEnd
    //     }
    //   });

  }

}

