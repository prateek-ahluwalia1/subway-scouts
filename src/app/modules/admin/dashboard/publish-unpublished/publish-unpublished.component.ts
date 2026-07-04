import { animate, query, stagger, style, transition, trigger } from '@angular/animations';
import { ChangeDetectionStrategy, ChangeDetectorRef, Component, OnInit, OnDestroy, ViewChild, ViewEncapsulation, Output, EventEmitter } from '@angular/core';
import { ServiceService } from 'app/services/service.service';
import { Subscription, Subject } from 'rxjs';
import { filter, takeUntil } from 'rxjs/operators';
import { FullScreenService } from '../full-screen.service';
import { GlobalVariable } from 'app/shared/global';
import moment from 'moment';

@Component({
  selector: 'app-publish-unpublished',
  templateUrl: './publish-unpublished.component.html',
  animations: [
    trigger('fadeIn', [
      transition(':enter', [
        style({ opacity: 0 }),
        animate('700ms', style({ opacity: 1 }))
      ])
    ]),
    trigger('stagger', [
      transition('* => *', [
        query(':enter', [
          style({ opacity: 0, transform: 'translateY(-15px)' }),
          stagger(100, [
            animate('700ms', style({ opacity: 1, transform: 'none' }))
          ])
        ], { optional: true })
      ])
    ])
  ],
  styles: [`
    .switch {
	    position: relative;
	    display: inline-block;
	    width: 100px;
	    height: 30px;
	    border: 2px solid #dcdcdc;
	    background: #e0e0e0;
	    box-shadow: 7px 7px 23px #bebebe, -7px -7px 23px #ffffff;
	    overflow: hidden;
	    border-radius: 60px;
      margin: 0;
    }

    .switch input {
	    opacity: 0;
	    width: 0;
	    height: 0;
    }

    .slider {
	    position: absolute;
	    cursor: pointer;
	    top: 0;
	    left: 0;
	    right: 0;
	    bottom: 0;
	    -webkit-transition: 0.5s;
	    transition: 0.5s;
    }

    input:checked + .slider:before {
	    background: white;
	    box-shadow: none;
    }

    input:focus + .slider {
	    box-shadow: 0 0 1px #2196f3;
    }

    .slider {
	    color: #9a9a9a;
	    display: flex;
	    align-items: center;
	    justify-content: center;
	    font-size: 11px;
	    font-family: sans-serif;
    }

    .slider--0 {
	    color: white;
	    font-weight: 600;
	    background-color: #49d84e;
    }

    .slider--1 div {
	    transition: 0.5s;
    }

    .slider--1 div {
	    position: absolute;
	    width: 100%;
	    height: 50%;
	    left: 0;
    }

    input:checked ~ .slider--1 div:first-child {
	    transform: translateY(-100%);
	    transition-delay: 1s;
    }

    input:checked ~ .slider--1 div:last-child {
	    transform: translateY(100%);
	    transition-delay: 1s;
    }

    input:checked ~ .slider--2 {
	    transform: translateX(100%);
	    transition-delay: 0.5s;
    }

    input:checked ~ .slider--3 {
	    transform: translateX(-100%);
	    transition-delay: 0s;
    }

    .slider--1 div:first-child {
	    transform: translateY(0);
	    top: 0;
	    background-color: #f3f3f3;
	    transition-delay: 0s;
    }

    .slider--1 div:last-child {
	    transform: translateY(0);
	    bottom: 0;
	    background-color: #f3f3f3;
	    border-top: 1px solid #e0e0e0;
	    transition-delay: 0s;
    }

    .slider--2 {
	    background-color: #e6e6e6;
	    transition-delay: 0.5s;
	    transform: translateX(0);
	    border-left: 1px solid #d2d2d2;
    }

    .slider--3 {
	    background-color: #d2d2d2;
	    transition-delay: 1s;
	    transform: translatex(0);
	    border-right: 1px solid #d2d2d2;
    }

    .circle {
      width: 12px;
      height: 12px;
      border-radius: 50%;
      margin-left: 5px;
      display: inline-block;
      position: relative;
    }

    .red-circle {
      background-color: red;
    }
  `],
  changeDetection: ChangeDetectionStrategy.OnPush
})

export class PublishUnpublishedComponent implements OnInit, OnDestroy {

  startDate: any;
  endDate: any;
  polling_data: any[] = [];
  filteredData: any[] = [];
  @ViewChild('fullScreen') divRef;
  isFullscreen: boolean = false;
  searchText: string = ''
  timeTracker = moment();
  @Output() dateRangeSelected = new EventEmitter<{ startDate: string, endDate: string }>();
  private _unsubscribeAll: Subject<any> = new Subject<any>();
  showUnpublished: boolean = true;

  constructor(private service: ServiceService, private _changeDetect: ChangeDetectorRef,
  private fullScreen: FullScreenService, public globals: GlobalVariable) {}

  ngOnInit() {
    this.service.publishUnPublish$.pipe(
      filter(res => res && res.data),
    takeUntil(this._unsubscribeAll))
    .subscribe(res => {
      this.polling_data = res.data;
      this.filteredData = this.showUnpublished ? res.data : this.filterUnpublishedData(res.data);
      this._changeDetect.markForCheck();
    });
  }

  ngOnDestroy(): void {
    this._unsubscribeAll.next(null);
    this._unsubscribeAll.complete();
  }

  openFullscreen() {
    const elem = this.divRef.nativeElement;
    this.fullScreen.toggleFullscreen(elem);
    this.isFullscreen = !this.isFullscreen;
  }

  receiveStartDate(data) {
    this.globals.start = data.format('MM-DD-YYYY');
  }

  receiveEndDate(data) {
    this.filteredData = []
    this.globals.end = data.format('MM-DD-YYYY')
    this.emitDateRange(this.globals.end, this.globals.start)
  }

  displayNextWeek() {
    this.globals.getWeekDays = [];
    this.getDays(1);
  }

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
    this.filteredData = []
    this.dateRangeSelected.emit({ startDate: start, endDate: end });
  }

  onCheckboxChange() {
    console.log('Checkbox state changed:', this.showUnpublished);
    if (!this.showUnpublished) {
      this.filteredData = this.filterUnpublishedData(this.polling_data)
    }
    else {
      this.filteredData = this.polling_data
    }
  }
  
  filterUnpublishedData(data) {
    return data.filter((row) => {
      for (const day of Object.keys(row)) {
        if (day === 'location') {
          continue;
        }
        const [publishedCount, unpublishedCount] = row[day].split(' | ');
        if (parseInt(unpublishedCount) > 0) {
          return true;
        }
      }
      return false;
    });
  }

  hasUnpublished(dayValue: string): boolean {
    if (!dayValue) {
      return false;
    }
    const [publishedCount, unpublishedCount] = dayValue.split(' | ');
    return parseInt(unpublishedCount) > 0;
  }

  trackById(index: number) {
    return index
  }

}
