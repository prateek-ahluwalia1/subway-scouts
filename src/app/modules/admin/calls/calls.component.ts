import { Component, OnInit } from '@angular/core';
import { Router } from '@angular/router';
import { TrackAdminActivityService } from 'app/services/track-admin-activity.service';

@Component({
  selector: 'app-calls',
  templateUrl: './calls.component.html',
  styleUrls: ['./calls.component.scss']
})
export class CallsComponent implements OnInit {

  routeId
  constructor(public router: Router, private trackAdmin: TrackAdminActivityService) {
    let routerId = localStorage.getItem('routerId')
    if (!routerId) {
      this.activity()
    }
   }

  calls = [
    { name: 'Dashboard', text: 'Display data about the calls, their history and reason for calling.', url: 'dashboard-calls', icon: 'heroicons_outline:clipboard-list', image:'/assets/images/nav-images/dashboard.jpg' },
    { name: 'Calls History', text: 'Display data of the previous calls.', url: 'calls', icon: 'heroicons_outline:phone-missed-call', image:'/assets/images/nav-images/callhistory.jpg' },
    { name: 'Recordings', text: 'Record all calls', url: 'calls-history', icon: 'heroicons_outline:phone-incoming', image:'/assets/images/nav-images/recording.png'},
  ]

  ngOnInit(): void {
  }

  ngOnDestroy() {
    localStorage.removeItem('routerId');

    this.trackAdmin.storeActivity('Exit Calls Page', 'Exit Calls Page', this.routeId).subscribe(res => {
    })
  }

  activity() {
    this.trackAdmin.storeActivity('Calls Page', 'Enter in Calls Page').subscribe(({ success, id }) => {
      if (success) {
        localStorage.setItem('routerId', id)
        this.routeId = id
      }
    })
  }
  callsHead(communiType: string) {
    this.router.navigate(['/callshead', communiType]);
  }

}
