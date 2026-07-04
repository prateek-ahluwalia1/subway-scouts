import { Component, OnInit } from '@angular/core';
import { FormArray, FormBuilder, FormControl, FormGroup, Validators } from '@angular/forms';
import { ConfirmBoxInitializer, DialogLayoutDisplay, AppearanceAnimation, DisappearanceAnimation, ButtonMaker, ButtonLayoutDisplay } from '@costlydeveloper/ngx-awesome-popup';
import { NgbModal } from '@ng-bootstrap/ng-bootstrap';
import { JobRoster1Service } from 'app/services/job-roster1.service';
import { SiteService } from 'app/services/site.service';
import { ToastServiceService } from 'app/services/toast-service.service';
import { GlobalVariable } from 'app/shared/global';
import moment from 'moment';
import { NgxSpinnerService } from 'ngx-spinner';
import { RosterServiceService } from '../../operations-modules/components/job-roster/roster-service.service';
export class Customers {
  id: number;
  name: string;
}
export class Sites {
  id: number;
  site_name: string;
}
@Component({
  selector: 'app-add-multiple-shifts',
  templateUrl: './add-multiple-shifts.component.html',
  styleUrls: ['./add-multiple-shifts.component.scss']
})
export class AddMultipleShiftsComponent implements OnInit {

  addShiftMultiple: FormGroup;
  multiiShifts: FormArray;
  selectedOption;
  isSubmitting = false;
  guards: any = []
  customers: Customers[] = [];
  sites: Sites[] = [];
  minDate: Date;
  constructor(private globals: GlobalVariable, private toast: ToastServiceService, private spiner: NgxSpinnerService,
    private fb: FormBuilder, private modalService: NgbModal, private rosterService: JobRoster1Service, private siteService: SiteService,
    private rosterServices: RosterServiceService) {
    this.minDate = new Date();
  }
  ngOnInit(): void {
    const id = localStorage.getItem('rosterId')
    if (id) {
      this.rosterService.accessRoster(id).subscribe(({ success, data, roster_name, roster_status }) => {
        if (success) {
          this.customers = data,
            this.globals.selectedCustomers = []
        }
      });
    } else {
      this.toast.toastNotification1('Something went wrong', 'Server Error')
    }

    this.addShiftMultiple = this.fb.group({
      multiiShifts: this.fb.array([

      ])
    });

    this.addShift();

  }

  close(data?) {
    this.modalService.dismissAll(data);
  }

  // this section is for add muliple shifts
  get multiiShiftsControls() {
    return this.addShiftMultiple.get('multiiShifts') as FormArray;
  }
  addShift() {
    const shift = this.fb.group({
      site_id: new FormControl('', Validators.required),
      guard_id: new FormControl(),
      date: new FormControl(Validators.required),
      start: new FormControl(),
      new_start: new FormControl(),
      end: new FormControl(),
      new_end: new FormControl(),
      shift_count: new FormControl(1)
    });

    this.multiiShiftsControls.push(shift);
  }
  // remove append shift
  removeShift(index: number) {
    this.multiiShiftsControls.removeAt(index);
  }



  updateShiftDateTime(index, start, end) {
    const shift = this.multiiShiftsControls.at(index);
    shift.get('new_start').setValue(start)
    shift.get('new_end').setValue(end)
  }

  // Here we add multiple shifts assigned and unassigned
  by_pass
  submitMultipleShifts(type?) {
    this.spiner.show()
    console.log(this.addShiftMultiple);

    if (this.isSubmitting) {
      this.spiner.show()
      return;
    }

    this.isSubmitting = true;


    if (this.addShiftMultiple.valid) {
      if (this.addShiftMultiple.value) {
        this.addShiftMultiple.value.multiiShifts.forEach((element, index) => {
          const { start, end } = this.AddTimeDate(element.start, element.end, element.date);
          this.updateShiftDateTime(index, start, end);
        });
      }
      this.addShiftMultiple.value.admin_id = this.globals.admin.admin_id
      this.addShiftMultiple.value.by_pass = this.by_pass ? this.by_pass : ''
      this.addShiftMultiple.value.roster_id = parseInt(localStorage.getItem('rosterId'))
      this.addShiftMultiple.value.type = type
      this.rosterService.submitMultipleShifts(this.addShiftMultiple.value).subscribe(({ success, message, by_pass }) => {
        if (success) {
          this.toast.toastNotification(message, 'Multiple Shifts Operation!')
          this.close('update')
          // this.sendFilterData()
          this.addShiftMultiple.reset();
          this.isSubmitting = false;
          this.by_pass = false;
        }
        else {
          this.isSubmitting = false;
          this.by_pass = by_pass
          this.addWithConflict(message, by_pass)
        }

        this.spiner.hide()
      })
    } else {
      this.toast.toastNotification1('Please check you have select customer, site and also a valid date', 'Invalid Form')
      this.isSubmitting = false;
      this.spiner.hide()

    }
  }

  // in this method we give date from add multiple shifts and get start and end
  AddTimeDate(starts, ends, day) {
    let newStart = moment(starts, "HH:mm");
    let newEnd = moment(ends, "HH:mm");

    if (newStart.isSame(newEnd)) {
      let status = 'Start and End Conflicts';
      let msg = 'Start and End time cannot be equal';
      this.toast.toastNotification1(msg, status);
      return;
    } else {
      const start = moment(day).format("MM-DD-YYYY") + " " + starts;
      let end;

      if (newEnd < newStart) {
        newEnd = moment(day).startOf('day');
        let endTime = newEnd.add(1, 'day');
        end = endTime.format("MM-DD-YYYY") + " " + ends;
      } else {
        end = moment(day).format("MM-DD-YYYY") + " " + ends;
      }

      return { start, end };
    }
  }

  // confirm conflict shift for add
  addWithConflict(msg, by_pass) {
    const newConfirmBox = new ConfirmBoxInitializer();
    newConfirmBox.setTitle('Confirm Action');
    newConfirmBox.setMessage(msg);
    // Choose layout color type
    newConfirmBox.setConfig({
      layoutType: DialogLayoutDisplay.DANGER, // SUCCESS | INFO | NONE | DANGER | WARNING
      animationIn: AppearanceAnimation.SWING, // BOUNCE_IN | SWING | ZOOM_IN | ZOOM_IN_ROTATE | ELASTIC | JELLO | FADE_IN | SLIDE_IN_UP | SLIDE_IN_DOWN | SLIDE_IN_LEFT | SLIDE_IN_RIGHT | NONE
      animationOut: DisappearanceAnimation.BOUNCE_OUT, // BOUNCE_OUT | ZOOM_OUT | ZOOM_OUT_WIND | ZOOM_OUT_ROTATE | FLIP_OUT | SLIDE_OUT_UP | SLIDE_OUT_DOWN | SLIDE_OUT_LEFT | SLIDE_OUT_RIGHT | NONE
      allowHtmlMessage: true,
      buttonPosition: 'center', // optional 
    });
    if (by_pass) {
      newConfirmBox.setButtons([
        new ButtonMaker('Confirm', 'Confirm', ButtonLayoutDisplay.SUCCESS),
        new ButtonMaker('Discard', 'Discard', ButtonLayoutDisplay.DANGER),
      ]);
    } else {
      newConfirmBox.setButtons([
        new ButtonMaker('Discard', 'Discard', ButtonLayoutDisplay.DANGER),
      ]);
    }


    // Simply open the popup and observe button click
    newConfirmBox.openConfirmBox$().subscribe(resp => {
      if (resp.clickedButtonID == 'Confirm') {
        this.addShiftMultiple.value.by_pass = true
        this.submitMultipleShifts()
        console.log(this.addShiftMultiple.value);
      }
      else {
        // this.jobroster.sendFilterData()
      }
    });
  }


  // collect data from child component customers,sites,guards

  customersIds: any[] = [];
  params: any;
  fromMultiCustomer: any = {};

  receiveDataFromChild(data: any, index: number) {
    console.log(data);

    this.fromMultiCustomer = data;
    if (data.value) {
      // this.customersIds = this.fromMultiCustomer.value.map(item => item.id);
      this.params = {
        customer_ids: [data?.value?.id],
        status: "all"
      };
      this.siteService.getAllSites(this.params).subscribe(({ success, data }) => {
        if (success) {
          this.sites[index] = data;
        }
      });
    }
  }

  receiveDataFromChildSitesingle(data: any, index: number) {
    this.getCusGuard(data?.id, index);
    const multiiShiftsArray = this.addShiftMultiple.get('multiiShifts') as FormArray;
    const formGroupAtIndex = multiiShiftsArray.at(index) as FormGroup;
    if (formGroupAtIndex) {
      formGroupAtIndex.get('site_id').setValue(data?.id);
    }
  }

  guardIndex: any = [];

  receiveDataFromChildGuardsSingle(data: any, index: number) {
    this.guardIndex = data;
    const guard_ids = this.guardIndex.value.map(item => item.id);
    const multiiShiftsArray = this.addShiftMultiple.get('multiiShifts') as FormArray;
    const formGroupAtIndex = multiiShiftsArray.at(index) as FormGroup;
    if (formGroupAtIndex) {
      formGroupAtIndex.get('guard_id').setValue(guard_ids);
    }
  }

  // end 

  getCusGuard(id, index) {
    this.rosterServices.getGuardBySite(id).subscribe(({ success, data }) => {
      if (success) {
        if (data) {
          data.forEach(element => {
            element.name = element.first_name + (element.middle_name ? ' ' + element.middle_name : '') + (element.last_name ? ' ' + element.last_name : '');
          });
        }
        this.guards[index] = data;

      }
    }, (error => {
    }))
  }
}
