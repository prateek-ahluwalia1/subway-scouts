import { ChangeDetectionStrategy, ChangeDetectorRef, Component, Input, OnInit } from '@angular/core';
import { FormControl, Validators } from '@angular/forms';
import { NgbActiveModal } from '@ng-bootstrap/ng-bootstrap';
import { CommonOneTwoService } from '../common-one-two.service';
import { RosterServiceService } from '../roster-service.service';
import moment from 'moment';
import { ToastServiceService } from 'app/services/toast-service.service';
import { Observable } from 'rxjs';
import { map, startWith } from 'rxjs/operators';
import { GlobalVariable } from 'app/shared/global';
import { NgxSpinnerService } from 'ngx-spinner';
import { AppearanceAnimation, ButtonLayoutDisplay, ButtonMaker, ConfirmBoxInitializer, DialogLayoutDisplay, DisappearanceAnimation } from '@costlydeveloper/ngx-awesome-popup';

@Component({
  selector: 'app-split',
  templateUrl: './split.component.html',
  styles: [`
    .ngx-timepicker-field-example{
      height:50px
    }`],
  changeDetection: ChangeDetectionStrategy.OnPush
})
export class SplitComponent implements OnInit {
  @Input() splitData: any;
  guards: any[] = [];
  submitButtonClicked: boolean = false;
  guard_id: any;
  clickedboxDate1: string;

  firstStartTime: string;
  firstEndTime: string;
  secondStartTime: string;
  secondEndTime: string;
  public filteredList: any = [];

  firstGuardControl = new FormControl();
  secondGuardControl = new FormControl();
  firstEndTimeControl = new FormControl('', Validators.required);
  secondStartTimeControl = new FormControl({ value: '', disabled: true }, Validators.required);

  filteredGuardsFirst: Observable<any[]>;
  filteredGuardsSecond: Observable<any[]>;

  selectedFirstGuard: any;
  selectedSecondGuard: any;
  showLocationError = false;

  constructor(
    public activeModal: NgbActiveModal,
    private cdr: ChangeDetectorRef,
    private toast: ToastServiceService,
    private commonService: CommonOneTwoService,
    private rosterService: RosterServiceService,
    private global: GlobalVariable,
    private spinner: NgxSpinnerService
  ) { }

  ngOnInit(): void {
    console.log(this.splitData);

    const start = moment(this.splitData?.shift.start, 'DD-MM-YYYY HH:mm').format('HH:mm');
    const end = moment(this.splitData?.shift.end, 'DD-MM-YYYY HH:mm').format('HH:mm');

    this.firstStartTime = start;
    this.secondEndTime = end;

    this.filteredGuardsFirst = this.firstGuardControl.valueChanges.pipe(
      startWith(''),
      map(value => this._filterGuards(value, 'first'))
    );

    this.filteredGuardsSecond = this.secondGuardControl.valueChanges.pipe(
      startWith(''),
      map(value => this._filterGuards(value, 'second'))
    );

    this.getBoxDate(this.splitData?.currentDay, this.splitData?.site, this.splitData?.calenderType);

    this.firstEndTimeControl.valueChanges.subscribe(value => {
      this.handleFirstEndTimeChange(value);
    });

    this.firstGuardControl.valueChanges.subscribe(() => {
      this.filteredGuardsSecond = this.secondGuardControl.valueChanges.pipe(
        startWith(''),
        map(value => this._filterGuards(value, 'second'))
      );
      this.cdr.markForCheck();
    });

    this.secondGuardControl.valueChanges.subscribe(() => {
      this.filteredGuardsFirst = this.firstGuardControl.valueChanges.pipe(
        startWith(''),
        map(value => this._filterGuards(value, 'first'))
      );
      this.cdr.markForCheck();
    });

    this.cdr.markForCheck();
  }

  getBoxDate(day: any, site: any, calType: string): void {
    if (calType === 'location') {
      if (site.customer_id) {
        this.getCusGuard(site.id);
      }
    } else if (calType === 'staff') {
      this.guard_id = site.id;
      this.getGuardSite(site.id);
    }
    this.clickedboxDate1 = moment(day.customFormat, 'ddd , DD/MM').format('ddd , DD MMM');
  }

  getCusGuard(id: number) {
    this.rosterService.getGuardBySite(id, this.splitData?.currentDay).subscribe(res => {
      if (res.success) {
        this.guards = res.data;
        this.filteredList = res.data;
        this.filteredGuardsFirst = this.firstGuardControl.valueChanges.pipe(
          startWith(''),
          map(value => this._filterGuards(value, 'first'))
        );
        this.filteredGuardsSecond = this.secondGuardControl.valueChanges.pipe(
          startWith(''),
          map(value => this._filterGuards(value, 'second'))
        );
        this.selectDefaultGuard();
        this.cdr.markForCheck();
      }
    }, (() => {
      this.toast.toastNotification1('There is something went wrong on fetching staff. Please contact with support team', 'Request Incomplete!');
    }));
  }

  getGuardSite(id: number) {
    this.rosterService.getGuardSite(id, this.splitData?.currentDay).subscribe(res => {
      if (res.success) {
        this.guards = res.data;
        this.filteredList = res.data;
        this.filteredGuardsFirst = this.firstGuardControl.valueChanges.pipe(
          startWith(''),
          map(value => this._filterGuards(value, 'first'))
        );
        this.filteredGuardsSecond = this.secondGuardControl.valueChanges.pipe(
          startWith(''),
          map(value => this._filterGuards(value, 'second'))
        );
        this.selectDefaultGuard();
        this.cdr.markForCheck();
      }
    }, (() => {
      this.toast.toastNotification1('There is something went wrong on fetching locations. Please contact with support team', 'Request Incomplete!');
    }));
  }

  private _filterGuards(value: any, control: string): any[] {
    const filterValue = typeof value === 'string' ? value.toLowerCase() : '';
    let filteredGuards = this.guards.filter(option =>
      option.first_name.toLowerCase().includes(filterValue) ||
      option.last_name.toLowerCase().includes(filterValue)
    );

    if (control === 'first' && this.secondGuardControl.value) {
      filteredGuards = filteredGuards.filter(guard => guard.id !== this.secondGuardControl.value.id);
    } else if (control === 'second' && this.firstGuardControl.value) {
      filteredGuards = filteredGuards.filter(guard => guard.id !== this.firstGuardControl.value.id);
    }

    return filteredGuards;
  }

  private selectDefaultGuard(): void {
    if (this.splitData?.shift.guard_id) {
      const guardId = parseInt(this.splitData.shift.guard_id, 10);
      const defaultGuard = this.guards.find(guard => guard.id === guardId);
      if (defaultGuard) {
        this.firstGuardControl.setValue(defaultGuard);
        this.splitData?.shift?.signin_status == 1 ? this.firstGuardControl.disable() : this.firstGuardControl.enable();
        this.cdr.markForCheck();
      }
    }
  }

  displayGuardFn(guard: any): string {
    return guard ? `${guard.first_name} ${guard.last_name}` : '';
  }

  handleFirstEndTimeChange(value: string): void {
    let firstEndTime = moment(value, 'HH:mm');
    const firstStartTime = moment(this.firstStartTime, 'HH:mm');
    let secondStartTime = moment(this.secondStartTime, 'HH:mm');
    let secondEndTime = moment(this.secondEndTime, 'HH:mm');
  
    if (firstEndTime.isBefore(firstStartTime)) {
      firstEndTime.add(1, 'day');
    }
    if (secondEndTime.isBefore(firstStartTime)) {
      secondEndTime.add(1, 'day');
    }
  
    if (firstEndTime.isAfter(firstStartTime) && firstEndTime.isBefore(secondEndTime)) {
      secondStartTime = firstEndTime.clone();
  
      this.secondStartTimeControl.setValue(secondStartTime.format('HH:mm'));
      this.secondStartTimeControl.disable();
    } else {
      this.secondStartTimeControl.setValue('');
      this.secondStartTimeControl.disable();
      this.toast.toastNotification1('First end time must be greater than start time and less than second end time.', 'Invalid Time');
    }
  
    this.firstStartTime = firstStartTime.format('HH:mm');
    this.secondEndTime = secondEndTime.format('HH:mm');
    this.cdr.markForCheck();
  }
  
  

  save(): void {
    const saveData = this.getFormData();
    saveData.publish_status = 0;
    this.onSubmit(saveData);
  }

  publish(): void {
    const publishData = this.getFormData();
    publishData.publish_status = 1;
    this.onSubmit(publishData);
  }

  onSubmit(data: any): void {
    let firstTime;
    let secondTime;
    if (data.first_start_time || data.first_end_time) {
      firstTime = this.global.AddTimeDate(data.first_start_time, data.first_end_time, this.splitData?.currentDay);
    }

    if (data.second_start_time || data.second_end_time) {
      secondTime = this.global.AddTimeDate(data.second_start_time, data.second_end_time, this.splitData?.currentDay);
    }

    let params = {
      first_shift: firstTime ? { start: firstTime.start, end: firstTime.end, guard_id: data.first_guard } : {},
      second_shift: secondTime ? { start: secondTime.start, end: secondTime.end, guard_id: data.second_guard } : {},
      common: { id: this.splitData?.shift?.roster_id, publish_status: data.publish_status },
    };
    this.saveRoster(params);
  }

  saveRoster(value): void {
    this.spinner.show();
    const isAdmin = this.global.admin.admin_id;
    const rosterId = localStorage.getItem('rosterId');
    value.admin = isAdmin;
    value.roster_id = rosterId;
    this.rosterService.splitShift(value).subscribe(res => {
      const status = 'Job Roster Operation';
      this.handleRosterResponse(res, status, value);
    }, (error) => {
      this.spinner.hide();
      this.toast.toastNotification1('Something went wrong. Please contact the support team.', 'Request Incomplete');
    });
  }

  handleRosterResponse(res, status, value): void {
    if (res.success) {
      this.toast.toastNotification(res.message, status);
      this.global.timestamp = Date.now();
      this.activeModal.close('submit');
    } else {
      this.addWithConflict(res, value, 'saveRoster');
    }
    this.activeModal.close('submit');
    this.global.timestamp = Date.now();
    this.spinner.hide();
  }

  addWithConflict(res, value, type?): void {
    const newConfirmBox = new ConfirmBoxInitializer();
    let layoutType;
    layoutType = res.message !== 'Sorry Staff On Leave!' ? DialogLayoutDisplay.SUCCESS : DialogLayoutDisplay.DANGER;

    let buttons: ButtonMaker[] = [];
    if (res.hide || res.message === 'Sorry Staff On Leave!') {
      newConfirmBox.setTitle('Access Denied!');
      newConfirmBox.setMessage(res.message);
      layoutType = DialogLayoutDisplay.DANGER;
      buttons.push(new ButtonMaker('Discard', 'Discard', ButtonLayoutDisplay.DANGER));
    } else {
      newConfirmBox.setTitle('Confirm Action');
      newConfirmBox.setMessage(res.message);
      buttons = [
        new ButtonMaker('Confirm', 'Confirm', ButtonLayoutDisplay.SUCCESS),
        new ButtonMaker('Discard', 'Discard', ButtonLayoutDisplay.DANGER)
      ];
    }

    newConfirmBox.setConfig({
      layoutType,
      animationIn: AppearanceAnimation.ZOOM_IN_ROTATE,
      animationOut: DisappearanceAnimation.ZOOM_OUT_WIND,
      allowHtmlMessage: true,
      buttonPosition: 'center',
    });
    newConfirmBox.setButtons(buttons);

    newConfirmBox.openConfirmBox$().subscribe(resp => {
      if (resp.clickedButtonID == 'Confirm') {
        if (type === 'saveRoster' || type === 'paste' || type === 'copy') {
          value.shift_confirm = 'yes';
          if (type === 'saveRoster') this.saveRoster(value);
        }
      } else {
        this.spinner.hide();
        this.activeModal.close('submit');
      }
    });
  }

  private getFormData(): any {
    const firstGuard = this.firstGuardControl.value;
    const secondGuard = this.secondGuardControl.value;

    return {
      first_start_time: this.firstStartTime,
      first_end_time: this.firstEndTimeControl.value,
      first_guard: firstGuard ? firstGuard.id : null,
      second_start_time: this.secondStartTimeControl.value,
      second_end_time: this.secondEndTime,
      second_guard: secondGuard ? secondGuard.id : null,
    };
  }

  checkWeek(lockWeek: boolean): boolean {
    return this.commonService.checkWeek(lockWeek);
  }
  

  clearSelection(type:string): void {
    if(type==='first'){
      this.firstGuardControl.setValue(null);    
      this.cdr.markForCheck();
    }
    else{
      this.secondGuardControl.setValue(null);
      this.cdr.markForCheck();

    }
  }
}
