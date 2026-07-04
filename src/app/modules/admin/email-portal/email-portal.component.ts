import { Component, OnInit } from '@angular/core';
import { Router } from '@angular/router';
import { TrackAdminActivityService } from 'app/services/track-admin-activity.service';

@Component({
  selector: 'app-email-portal',
  templateUrl: './email-portal.component.html',
  styleUrls: ['./email-portal.component.scss']
})
export class EmailPortalComponent implements OnInit {

  routeId
  constructor(public router: Router, private trackAdmin: TrackAdminActivityService) {
    let routerId = localStorage.getItem('routerId')
    if (!routerId) {
      this.activity()
    }
   }

   ngOnDestroy() {
    localStorage.removeItem('routerId');

    this.trackAdmin.storeActivity('Exit Email Portal Page', 'Exit Email Portal Page', this.routeId).subscribe(res => {
    })
  }

  activity() {
    this.trackAdmin.storeActivity('Email Portal Page', 'Enter in Email Portal Page').subscribe(({ success, id }) => {
      if (success) {
        localStorage.setItem('routerId', id)
        this.routeId = id
      }
    })
  }

  emailHead = [
    { name: 'Quick Email', text: 'Used to send the email to the existing and new staff instantly.', url: 'quick-email', icon: 'heroicons_outline:chat-alt', image:'/assets/images/nav-images/quickemail.jpg' },
    { name: 'Email Template', text: 'Used for creating template emails to send to the staff.', url: 'email-template', icon: 'heroicons_outline:chat-alt', image:'/assets/images/nav-images/emailtemplate.jpg' },
    { name: 'Email Signature', text: 'Used for creating email Signature to identifies who you are.', url: 'email-signature', icon: 'heroicons_outline:chat-alt', image:'/assets/images/nav-images/emailsignature.png'},
  ]

  ngOnInit(): void {
  }

  emailPortal(communiType: string) {
    this.router.navigate(['/email-portal', communiType]);
  }

}
