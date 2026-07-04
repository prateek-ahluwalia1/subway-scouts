import { ChangeDetectionStrategy, ChangeDetectorRef, Component, EventEmitter, OnDestroy, OnInit, Output, ViewChild, ViewEncapsulation } from '@angular/core';
import { FormBuilder, FormControl, FormGroup } from '@angular/forms';
import { MatDatepickerInputEvent } from '@angular/material/datepicker';
import { ServiceService } from 'app/services/service.service';
import { ToastServiceService } from 'app/services/toast-service.service';
import moment from 'moment';
import { Subject, Subscription, filter, takeUntil } from 'rxjs';
import { FullScreenService } from '../full-screen.service';
import { GlobalVariable } from 'app/shared/global';
import { NgbModal } from '@ng-bootstrap/ng-bootstrap';
import { ButtonLayoutDisplay, ButtonMaker, DialogInitializer, DialogLayoutDisplay } from '@costlydeveloper/ngx-awesome-popup';
import { StaffResponseComponent } from './staff-response/staff-response.component';
import { CustomeLoaderComponent } from '../../custome-loader/custome-loader.component';

@Component({
  selector: 'app-staff-confirmation',
  templateUrl: './staff-confirmation.component.html',
  styles: [`
    .staff-confirmation-jobs {
      min-height: 450px;
      max-height: 450px;
    }
  `]
})

export class StaffConfirmationComponent implements OnInit, OnDestroy {

  @ViewChild('fullScreen') divRef;
  isFullscreen: boolean = false;
  timeTracker = moment();
  @Output() dateRangeSelected = new EventEmitter<{ startDate: string, endDate: string }>();
  private _staff: Subject<any> = new Subject<any>();
  ConfirmstaffData: any[] = [];
  confirmShiftCount;
  UnConfirmStaffdata: any[] = [];
  rejectedShiftsCount;
  unconfirmShiftCount
  modalContent
  title

  constructor(private service: ServiceService, private _changeDetect: ChangeDetectorRef, private fullScreen: FullScreenService,
  private globals: GlobalVariable, private modalService: NgbModal) {}

  ngOnInit() {
    this.service.staffConfirm$.pipe(
      filter(res => res && res.success),
    takeUntil(this._staff))
    .subscribe(res => {
      this.ConfirmstaffData = res?.confirmedShifts;
      this.UnConfirmStaffdata = res?.pendingShifts;
      this.confirmShiftCount = res?.confirmedShiftsCount;
      this.rejectedShiftsCount = res?.rejectedShiftsCount;
      this.unconfirmShiftCount = res?.unconfirmedShiftsCount;
      this._changeDetect.markForCheck();
    });
  }

  ngOnDestroy() {
    this._staff.next(null);
    this._staff.complete();
  }

  openFullscreen() {
    const elem = this.divRef.nativeElement;
    this.fullScreen.toggleFullscreen(elem)
    this.isFullscreen = !this.isFullscreen
  }

  trackByFn(index: number, item: any): number {
    return index;
  }

  ///hira
  displayPrevWeek() {
    this.globals.getWeekDays = [];
    this.getDays(-1);
  }

  getDays(e, type?) {
    if (e == 0) {
      this.timeTracker = moment();
    } else {
      this.timeTracker.add(e, 'weeks');
    }
    // Find start and end of the week
    var startOfWeek = this.timeTracker.clone().startOf('isoWeek');
    this.globals.start = startOfWeek.format('MM-DD-YYYY');
    var endOfWeek;
    if (type == 'two') {
      endOfWeek = this.timeTracker.clone().add(1, 'weeks').endOf('isoWeek');
      this.globals.end = endOfWeek.format('MM-DD-YYYY');
    } else {
      endOfWeek = this.timeTracker.clone().endOf('isoWeek');
      this.globals.end = endOfWeek.format('MM-DD-YYYY');
    }
    this.globals.selectedDate = `${startOfWeek.format('MMM D')} - ${endOfWeek.format('D, YYYY')}`;
    var day = startOfWeek;
    while (day.isSameOrBefore(endOfWeek)) {
      this.globals.getWeekDays.push(moment(day).format("ddd , DD/MM"));
      day = day.add(1, 'days');
    }
    this.emitDateRange(startOfWeek.format('MM-DD-YYYY'), endOfWeek.format('MM-DD-YYYY'))
    return this.globals.getWeekDays;
  }

  emitDateRange(start: string, end: string) {
    this.dateRangeSelected.emit({ startDate: start, endDate: end });
  }

  receiveStartDate(data) {
    this.globals.start = data.format('MM-DD-YYYY');
  }

  receiveEndDate(data) {
    this.globals.end = data.format('MM-DD-YYYY')
    this.emitDateRange(this.globals.end, this.globals.start)
  }

  displayNextWeek() {
    this.globals.getWeekDays = [];
    this.getDays(1);
  }

  openLg(title: string) {
    const dialogPopup = new DialogInitializer(StaffResponseComponent);
    dialogPopup.setConfig({
      width: '800px',
      layoutType: DialogLayoutDisplay.INFO // SUCCESS | INFO | NONE | DANGER | WARNING

    });
    dialogPopup.setCustomData({ type: title, ConfirmstaffData: this.ConfirmstaffData, UnConfirmStaffdata: this.UnConfirmStaffdata });
    dialogPopup.setConfig({
      width: '800px',
      loaderComponent: CustomeLoaderComponent,
      layoutType: DialogLayoutDisplay.NONE // SUCCESS | INFO | NONE | DANGER | WARNING
    });
    dialogPopup.setButtons([
      new ButtonMaker('Close', 'close', ButtonLayoutDisplay.DARK),
    ]);
    dialogPopup.openDialog$().subscribe(resp => {
      console.log('dialog response: ', resp);
    });
    // this.title = title;
    // this.modalService.open(longContent, { scrollable: true });
  }

  handleBrokenImage(event: Event) {
    const imgElement = event.target as HTMLImageElement;
    imgElement.src = '../../../../assets/images/avatars/user-128.png';
  }

}
