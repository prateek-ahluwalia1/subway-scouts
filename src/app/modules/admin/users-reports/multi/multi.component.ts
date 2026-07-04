import { ChangeDetectionStrategy, Component, OnInit } from '@angular/core';
import moment from 'moment';

@Component({
  selector: 'app-multi',
  templateUrl: './multi.component.html',
  styleUrls: ['../users-reports.component.scss'],
  changeDetection: ChangeDetectionStrategy.OnPush
})
export class MultiComponent implements OnInit {
  dateRange: any;

  start = moment().startOf('week').add(1, 'days').toDate();
  end = moment().endOf('week').add(1, 'days').toDate();
  constructor() { }

  ngOnInit(): void {
  }

  getStartEnd(event: any) {
    this.dateRange = event
  }

}
