import {Component, ElementRef, HostListener, OnInit, ViewChild} from '@angular/core';
import * as moment from 'moment';
import * as range from 'lodash.range';
import { GlobalVariable } from 'app/shared/global';
export interface CalendarDate {
  mDate: moment.Moment;
  selected?: boolean;
  today?: boolean;
}

@Component({
  selector: 'app-ento-design-weekly',
  templateUrl: './ento-design-weekly.component.html',
  styleUrls: ['./ento-design-weekly.component.scss']
})
export class EntoDesignWeeklyComponent implements OnInit {

  
  public currentDate: moment.Moment;
  public namesOfDays = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
  public weeks: Array<CalendarDate[]> = [];

  public selectedDate;
  public selectedStartWeek;
  public selectedEndWeek;
  public show: boolean;

  @ViewChild('calendar', {static: true}) calendar;
  selectedWeek: any;

  @HostListener('document:click', ['$event'])
  clickOut(event) {
    if (!this.eRef.nativeElement.contains(event.target)) {
      this.show = false;
    }
  }

  constructor(
    private eRef: ElementRef,
    public globals:GlobalVariable) {
  }

  ngOnInit() {
    this.currentDate = moment();
    this.selectedStartWeek = moment().weekday(1);
    
    this.selectedEndWeek = moment().weekday(7);
    
    this.selectedDate = `${this.selectedStartWeek.format('MMM D')} - ${this.selectedEndWeek.format('MMM D')}`;
    this.generateCalendar();
    console.log('nnnnn  ',this.selectedDate);
    this.selectedWeek = `${ this.selectedStartWeek.format('MMM D')} - ${this.selectedEndWeek.format('MMM D')}`;
console.log("in constructoe week ",this.selectedWeek);

    
    
  }

  private generateCalendar(): void {
    const dates = this.fillDates(this.currentDate);
    const weeks = [];
    while (dates.length > 0) {
      weeks.push(dates.splice(0, 7));
    }
    this.weeks = weeks;
  }

  private fillDates(currentMoment: moment.Moment) {
    // index first day of month in week
    const firstOfMonth = moment(currentMoment).startOf('month').day()-1;
    // index last day of month  in week
    const lastOfMonth = moment(currentMoment).endOf('month').day();

    const firstDayOfGrid = moment(currentMoment).startOf('month').subtract(firstOfMonth, 'days');
    // get last start of week + week
    const lastDayOfGrid = moment(currentMoment).endOf('month').subtract(lastOfMonth, 'days').add(7, 'days');

    const startCalendar = firstDayOfGrid.date();


    return range(startCalendar, startCalendar + lastDayOfGrid.diff(firstDayOfGrid, 'days')).map((date) => {
      const newDate = moment(firstDayOfGrid).date(date);
      return {
        today: this.isToday(newDate),
        selected: this.isSelected(newDate),
        mDate: newDate,
      };
    });
  }

  public prevMonth(): void {
    this.currentDate = moment(this.currentDate).subtract(1, 'months');
    this.generateCalendar();
  }

  public nextMonth(): void {
    this.currentDate = moment(this.currentDate).add(1, 'months');
    this.generateCalendar();
  }

  // public isDisabledMonth(currentDate): boolean {
  //   const today = moment();
  //   return moment(currentDate).isBefore(today, 'months');
  // }

  private isToday(date: moment.Moment): boolean {
    return moment().format('YYYY-MM-DD') === moment(date).format('YYYY-MM-DD');
  }

  private isSelected(date: moment.Moment): boolean {
    return moment(date).isBefore(this.selectedEndWeek) && moment(date).isAfter(this.selectedStartWeek)
      || moment(date.format('YYYY-MM-DD')).isSame(this.selectedStartWeek.format('YYYY-MM-DD'))
      || moment(date.format('YYYY-MM-DD')).isSame(this.selectedEndWeek.format('YYYY-MM-DD'));
  }

  public isDayBeforeLastSat(date: moment.Moment): boolean {
    const lastSat = moment().weekday(-1);
    return moment(date).isSameOrBefore(lastSat);
  }

  public isSelectedMonth(date: moment.Moment): boolean {
    return moment(date).isSame(this.currentDate, 'month');
  }



  public selectDate(date: CalendarDate[]) {
    this.selectedStartWeek = moment(date[0].mDate);
    
    this.selectedEndWeek = moment(date[6].mDate);
    var weekStart = this.selectedStartWeek.clone().startOf('isoWeek');
    this.globals.entoSelectedWeek = [];
    for (var i = 0; i <= 6; i++) {
       this.globals.entoSelectedWeek.push(moment(weekStart).add(i, 'days').format("ddd , DD/MM"));
     
     // this.globals.getWeekDays.push(moment(weekStart).add(i, 'days').format("ddd"));
    };
    console.log('this.globals.getWeekDays',this.globals.entoSelectedWeek);
    
    this.selectedDate = `${ this.selectedStartWeek.format('MMM D')} - ${this.selectedEndWeek.format('MMM D')}`;
    this.selectedWeek = `${ this.selectedStartWeek.format('MMM D')} - ${this.selectedEndWeek.format('MMM D')}`;
    console.log('this.selected week new ',this.selectedWeek);
    console.log('selectedDate  ',this.selectedDate );
    
    
    this.generateCalendar();
    this.show = !this.show;
  }

}
