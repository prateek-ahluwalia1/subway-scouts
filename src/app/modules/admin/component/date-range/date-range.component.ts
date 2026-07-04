import { Component, EventEmitter, Input, OnInit, Output } from '@angular/core';
import { FormGroup, FormControl } from '@angular/forms';
import { DateAdapter } from '@angular/material/core';
import moment from 'moment';

@Component({
  selector: 'app-date-range',
  templateUrl: './date-range.component.html',
  styleUrls: ['./date-range.component.scss']
})
export class DateRangeComponent implements OnInit {

  @Input() start;
  @Input() end;
  @Input() placeholder1;
  @Input() placeholder2;
  @Input('label') label; 
  @Output() dateRange: EventEmitter<any> = new EventEmitter()
  range = new FormGroup({
    start: new FormControl<Date | null>(null),
    end: new FormControl<Date | null>(null),
  });

  constructor(public dateAdapter: DateAdapter<Date>,) {
    this.dateAdapter.setLocale('en-AU');
  }

  ngOnInit(): void {
    if (this.start && this.end) {
      this.range.setValue({
        start: this.start,
        end: this.end
      });
    }

    this.dateRange.emit(this.range.value)

    this.range.valueChanges.subscribe(val => {
      const { start, end } = val;
      if (start && end) {
        this.dateRange.emit(val)
      }
    });
  }

}
