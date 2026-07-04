import { Component, OnInit } from '@angular/core';
import { Router } from '@angular/router';
import { TrackAdminActivityService } from 'app/services/track-admin-activity.service';

@Component({
  selector: 'app-sms-portal',
  templateUrl: './sms-portal.component.html',
  styleUrls: ['./sms-portal.component.scss'],
})
export class SmsPortalComponent implements OnInit {

  routeId
  constructor(public router: Router, private trackAdmin: TrackAdminActivityService) {
    let routerId = localStorage.getItem('routerId')
    if (!routerId) {
      this.activity()
    }
   }

  smsHead = [
    { name: 'Quick SMS', text: 'Used to send the message to the existing and new staff instantly.', url: 'quick-sms', icon: 'heroicons_outline:chat-alt', image: '/assets/images/nav-images/quicksms.png'},
    { name: 'SMS Portal History', text: 'Maintain a comprehensive record of all sent messages.', url: 'sms-portal-history', icon: 'heroicons_outline:chat-alt', image: '/assets/images/nav-images/sendsmsrecord.png'},
    { name: 'Template', text: 'Used for creating template messages to send to the staff.', url: 'template', icon: 'heroicons_outline:chat-alt', image: '/assets/images/nav-images/smstemplate.png'},
    { name: 'Chat History', text: 'Display record of the sent and received messages. ', url: 'chat-history', icon: 'heroicons_outline:chat-alt', image: '/assets/images/nav-images/smsportal.png'},
  ]

  ngOnInit(): void {
  }

  ngOnDestroy() {
    localStorage.removeItem('routerId');

    this.trackAdmin.storeActivity('Exit SMS Portal Page', 'Exit SMS Portal Page', this.routeId).subscribe(res => {
    })
  }

  activity() {
    this.trackAdmin.storeActivity('SMS Portal Page', 'Enter in SMS Portal Page').subscribe(({ success, id }) => {
      if (success) {
        localStorage.setItem('routerId', id)
        this.routeId = id
      }
    })
  }

  smsPortal(communiType: string) {
    this.router.navigate(['/sms-portal', communiType]);
  }

}
