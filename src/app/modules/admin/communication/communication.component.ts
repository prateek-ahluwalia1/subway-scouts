import { Component, OnInit } from '@angular/core';
import { Router } from '@angular/router';
import { PermissionsService } from 'app/services/permissions.service';
import { TrackAdminActivityService } from 'app/services/track-admin-activity.service';

@Component({
  selector: 'app-communication',
  templateUrl: './communication.component.html',
})

export class CommunicationComponent implements OnInit {

  routeId
  comPermissions
  searchText
  tabs: any = ['All', 'Announcement', 'Induction', 'SMS', 'Calls', 'E-Mail']
  icons: string[] = ['icon-bolt', 'icon-bullhorn', 'icon-certificate', 'icon-mobile-phone', 'icon-phone', 'icon-envelope-alt'];
  selectedTab: string = 'All';
  newArray: any[] = [];

  constructor(public router: Router, private trackAdmin: TrackAdminActivityService,
    private permissionService: PermissionsService) {
    let routerId = localStorage.getItem('routerId')
    if (!routerId) {
      this.activity()
    }
  }
  
  reports = [
    // { name: 'Chat Box', text: '', url: 'chat-app', icon: 'mat_outline:announcement', image: '/assets/images/nav-images/quicksms.png', enabled: false },
    { name: 'Announcement', text: '', url: 'announcement', icon: 'mat_outline:announcement', image: '/assets/images/nav-images/announcement.png', enabled: false },
    { name: 'Induction', text: '', url: 'induction', icon: 'mat_outline:notification_important', image: '/assets/images/nav-images/induction.png', enabled: false },
    // { name: 'Quick SMS', text: '', url: 'quick-sms', icon: 'heroicons_outline:chat-alt', image: '/assets/images/nav-images/quicksms.png', enabled: false },
    // { name: 'SMS Portal History', text: '', url: 'sms-portal-history', icon: 'heroicons_outline:chat-alt', image: '/assets/images/nav-images/smsportalhistory.jpg', enabled: false },
    // { name: 'Template', text: '', url: 'template', icon: 'heroicons_outline:chat-alt', image: '/assets/images/nav-images/smstemplate.png', enabled: false },
    // { name: 'Quick Email', text: '', url: 'quick-email', icon: 'heroicons_outline:chat-alt', image: '/assets/images/nav-images/quickemail.png', enabled: false },
    // { name: 'Email Template', text: '', url: 'email-template', icon: 'heroicons_outline:chat-alt', image: '/assets/images/nav-images/emailtemplate.png', enabled: false },
    // { name: 'Email Signature', text: '', url: 'email-signature', icon: 'heroicons_outline:chat-alt', image: '/assets/images/nav-images/emailsignature.jpg', enabled: false },
    // { name: 'Outlook', text: '', url: 'mailbox', icon: 'bar_chart', image: '/assets/images/nav-images/outlook_email.jpg', enabled: false },
    // { name: 'Chat History', text: '', url: 'chat-history', icon: 'heroicons_outline:chat-alt', image: '/assets/images/nav-images/chat_history.png', enabled: false },
    // { name: 'Calls Dashboard', text: '', url: 'dashboard-calls', icon: 'heroicons_outline:clipboard-list', image: '/assets/images/nav-images/calls_dashboard.png', enabled: false },
    // { name: 'Calls History', text: '', url: 'calls', icon: 'heroicons_outline:phone-missed-call', image: '/assets/images/nav-images/callhistory.png', enabled: false },
    // { name: 'Calls Recordings', text: '', url: 'calls-history', icon: 'heroicons_outline:phone-incoming', image: '/assets/images/nav-images/call recording.png', enabled: false },
  ]

  ngOnInit(): void {
    this.comPermissions = this.permissionService.getPermissionsByTitle('Communications');
    for (const report of this.reports) {
      const matchingChild = this.comPermissions?.childPage?.find(child => child.title === report.name);
      if (matchingChild) {
        report.enabled = matchingChild.enabled;
      } else {
        report.enabled = false;
      }
    }
    this.getTabData(this.selectedTab)
  }

  ngOnDestroy() {
    // localStorage.removeItem('routerId');

    // this.trackAdmin.storeActivity('Exit Communication Page', 'Exit Communication Page', this.routeId).subscribe(res => {
    // })
  }

  activity() {
    this.trackAdmin.storeActivity('Communication Page', 'Enter in Communication Page').subscribe(({ success, id }) => {
      if (success) {
        localStorage.setItem('routerId', id)
        this.routeId = id
      }
    })
  }

  jobTracker(communiType: string, name) {
    if (name === 'Announcement' || name === 'Induction') {
      this.router.navigate(['/communication', communiType]);
    }
    else if (name === 'Quick SMS' || name === 'SMS Portal History' || name === 'Template' || name === 'Chat History') {
      this.router.navigate(['/sms-portal', communiType]);
    }
    else if (name === 'Calls Dashboard' || name === 'Calls History' || name === 'Calls Recordings') {
      this.router.navigate(['/calls', communiType]);
    }
    else if (name === 'Quick Email' || name === 'Email Template' || name === 'Email Signature') {
      this.router.navigate(['/email-portal', communiType]);
    }
    else {
      this.router.navigate([communiType]);
    }
  }

  getTabData(type) {
    if (type == 'All') {
      this.newArray = this.reports;
    }
    else if (type === 'SMS') {
      this.newArray = this.reports.filter(item =>
        item.name === 'Quick SMS' ||
        item.name === 'SMS Portal History' ||
        item.name === 'Template' ||
        item.name === 'Chat History'
      );
    } else if (type === 'Calls') {
      console.log('find calls');

      this.newArray = this.reports.filter(item =>
        item.name === 'Calls Dashboard' ||
        item.name === 'Calls History' ||
        item.name === 'Calls Recordings'
      );
    }
    else if (type === 'E-Mail') {
      this.newArray = this.reports.filter(item =>
        item.name === 'Quick Email' || item.name === 'Email Template' || item.name === 'Email Signature' || item.name === 'Outlook'
      );
    } else {
      this.newArray = this.reports.filter(item => item.name === type);

    }
  }

  goToEmail(type) {
    localStorage.setItem('type', type)
    this.router.navigate(['email']);
  }

}
