import { Component, OnInit } from '@angular/core';
import { Router } from '@angular/router';
import { PermissionsService } from 'app/services/permissions.service';

@Component({
  selector: 'app-emails',
  templateUrl: './emails.component.html',
  styleUrls: ['./emails.component.scss']
})
export class EmailsComponent implements OnInit {


  outlooks = [
    { name: 'Outlook', text: 'Used to display report of tasks assigned to a specific staff and its detail.', url: 'emails', icon: 'bar_chart', image: '/assets/images/nav-images/taskreport.png', enabled: false },
  ]

  emails = [
    { name: 'Quick Email', text: 'Used to send the email to the existing and new staff instantly.', url: 'quick-email', icon: 'heroicons_outline:chat-alt', image: '/assets/images/nav-images/quickemail.jpg', enabled: false },
    { name: 'Email Template', text: 'Used for creating template emails to send to the staff.', url: 'email-template', icon: 'heroicons_outline:chat-alt', image: '/assets/images/nav-images/emailtemplate.jpg', enabled: false },
    { name: 'Email Signature', text: 'Used for creating email Signature to identifies who you are.', url: 'email-signature', icon: 'heroicons_outline:chat-alt', image: '/assets/images/nav-images/emailsignature.png', enabled: false },
  ]
  adminPermissions: any
  constructor(private router: Router, private permissionService: PermissionsService) { }

  ngOnInit(): void {
    this.adminPermissions = this.permissionService.getPermissionsByTitle('Onboarding');
    console.log("All users", this.adminPermissions)

    for (const user of this.emails) {
      const matchingChild = this.adminPermissions?.childPage?.find(child => child.title === user.name);
      if (matchingChild) {
        user.enabled = matchingChild.enabled;
      } else {
        user.enabled = false;
      }
    }
    for (const user of this.outlooks) {
      const matchingChild = this.adminPermissions?.childPage?.find(child => child.title === user.name);
      if (matchingChild) {
        user.enabled = matchingChild.enabled;
      } else {
        user.enabled = false;
      }
    }
  }

  goToEmail(communiType: string) {
    this.router.navigate(['/email-portal', communiType]);
  }
  email(communiType: string) {
    this.router.navigate([communiType]);
  }

}
