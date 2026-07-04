import { Component, OnInit, OnDestroy } from '@angular/core';
import { AppStatusService } from 'app/services/app-status.service';
import { TrackAdminActivityService } from 'app/services/track-admin-activity.service';
import { NgxSpinnerService } from 'ngx-spinner';

@Component({
  selector: 'app-app-status',
  templateUrl: './app-status.component.html',
  styleUrls: ['./app-status.component.scss']
})
export class AppStatusComponent implements OnInit, OnDestroy {

  processedAppData: any[] = [];
  mainArray: any[] = [];
  interval: any;
  routeId: any;

  constructor(private appdata: AppStatusService, private trackAdmin: TrackAdminActivityService) {
    let routerId = localStorage.getItem('routerId')
    if (!routerId) {
      this.activity()
    }
  }

  ngOnInit(): void {
    this.getAppDataAndUpdate(false);
    setTimeout(() => {
      this.getAppDataAndUpdate(true);
    }, 20000);
    this.polling();
  }

  ngOnDestroy(): void {
    clearInterval(this.interval);
    localStorage.removeItem('routerId');
    this.trackAdmin.storeActivity('Exit Job Roster Page', 'Exit Job Roster Page', this.routeId).subscribe();
  }

  getAppDataAndUpdate(type: boolean): void {
    this.processedAppData = []
    this.appdata.getAppData().subscribe(({ data }) => {
      this.mainArray = data;
      if (data.length > this.processedAppData.length) {
        this.processData(data, type);
      }
    });
  }

  polling(): void {
    this.interval = setInterval(() => {
      this.processedAppData = [];
      this.getAppDataAndUpdate(true);
    }, 1 * 60 *1000);
  }

  processData(data: any[], type): void {
    console.log(data);
    
    data.forEach(val => {
      const to_time = new Date().getTime();
      const to_timeInSeconds = Math.floor(to_time / 1000);
      let appStatus = 'Just Started';
      let badgeClass = 'status-JustStarted';
      let diff = 0;

      if (val.guardz.last_seen !== null) {
        const from_time = val.guardz.last_seen;
        diff = Math.round(Math.abs((to_timeInSeconds - from_time) / 60) * 100) / 100;
      }

      if (!val.guardz.in_radius) {
        const from_time = val.guardz.last_seen || val.activity?.signin_time;
        diff = Math.round(Math.abs((to_timeInSeconds - from_time) / 60) * 100) / 100;

        badgeClass = 'status-warning';
        appStatus = (diff > 3) ? 'Closed' : 'Left Location';
      }
      else if (val.guardz && val.guardz.last_seen !== null) {
        const from_time = val.guardz.last_seen;
        diff = Math.round(Math.abs((to_timeInSeconds - from_time) / 60) * 100) / 100;

        badgeClass = (diff < 3) ? 'status-success' : 'status-warning';
        appStatus = (diff < 3) ? 'Running' : 'Closed';
      }
      else {
        const from_time = new Date(val.activity?.signin_time).getTime();
        diff = Math.round(Math.abs((to_timeInSeconds - from_time) / 60) * 100) / 100;

        appStatus = (diff < 3) ? 'Just Started' : 'Closed';
        badgeClass = (diff < 3) ? 'status-JustStarted' : 'status-warning';
      }
      console.log(type);
      
      if ((appStatus == 'Closed' || appStatus == 'Left Location') && type) {
        this.sendNoti(val.guard_id, appStatus)
      }
      this.processedAppData.push({
        ...val,
        appStatus: type ? appStatus : 'Please Wait',
        badgeClass: badgeClass,
        site: val.site || { site_name: 'N/A' },
      });
    });
  }

  activity() {
    this.trackAdmin.storeActivity('App Status', `Enter in App Status`).subscribe(({ success, id }) => {
      if (success) {
        localStorage.setItem('routerId', id)
        this.routeId = id
      }
    })
  }

  sendNoti(id, status) {
    let data = {
      id: id,
      type: status
    }
    this.appdata.sendNoti(data).subscribe(res => {
      console.log(res);

    })
  }
}
