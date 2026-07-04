import { ChangeDetectionStrategy, ChangeDetectorRef, Component, OnDestroy, OnInit } from '@angular/core';
import { DateAdapter } from '@angular/material/core';
import { AdminService } from 'app/services/admin.service';
import { TrackAdminActivityService } from 'app/services/track-admin-activity.service';
import moment from 'moment';

@Component({
  selector: 'app-task-log',
  templateUrl: './task-log.component.html',
  styleUrls: ['./task-log.component.scss'],
  changeDetection: ChangeDetectionStrategy.OnPush
})
export class TaskLogComponent implements OnInit, OnDestroy {

  start: Date;
  end: Date;
  selectedAdmin: any;
  admins: any[] = [];
  displayAdminactivity: any = [];
  Adminactivity: any = [];

  constructor(public dateAdapter: DateAdapter<Date>, private adminservice: AdminService, private trackAdmin: TrackAdminActivityService,
    private cdr: ChangeDetectorRef) {

    this.adminservice.getAdmin().subscribe(
      (res) => {
        if (res.success) {
          this.admins = res.data;
        }
      }, (error) => {
        console.log(error)
      }
    );
    this.dateAdapter.setLocale('en-AU');

    const currentMoment = moment();
    this.start = currentMoment.startOf('week').toDate();
    this.end = currentMoment.endOf('week').toDate();


  }

  ngOnDestroy() {

    this.trackAdmin.storeActivity('Admin Log', `Exit in admin log`, localStorage.getItem('routerId')).subscribe(res => {
      if (res.success) {
        localStorage.removeItem('routerId');
      }
    })
  }

  ngOnInit(): void {
    let routerId = localStorage.getItem('routerId')
    if (!routerId) {
      this.activity()
    }
  }

  receiveDataFromChildSingle(data: any) {
    this.selectedAdmin = data.value
  }

  SearchResult() {
    const startDate = moment(this.start).format('YYYY-MM-DD');
    const endDate = moment(this.end).format('YYYY-MM-DD');
    let data = {
      user_id: this.selectedAdmin.id,
      start_date: startDate,
      end_date: endDate
    }
    this.trackAdmin.getActivity(data).subscribe(res => {
      if (res.success) {
        this.displayAdminactivity = Object.keys(res.data);
        this.Adminactivity = res.data;
        this.cdr.markForCheck()
      }
    })
  }

  getLastTwoNamesFromUrl(url) {
    const parts = url.split('/').filter(part => part.trim() !== '');
    const lastPart = parts[parts.length - 1];
    const secondLastPart = parts[parts.length - 2];

    if (secondLastPart.includes('#')) {
      return lastPart.toUpperCase();
    }

    if (parts.length >= 2) {
      return `${secondLastPart.toUpperCase()}/${lastPart.toUpperCase()}`;
    }

    return lastPart.toUpperCase();
  }

  activity() {
    this.trackAdmin.storeActivity('Admin Log', `Enter in admin log`).subscribe(({ success, id }) => {
      if (success) {
        localStorage.setItem('routerId', id)
      }
    })
  }

}
