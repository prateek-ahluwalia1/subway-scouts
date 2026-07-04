import { Component, OnInit } from '@angular/core';
import { Router } from '@angular/router';
import { PermissionsService } from 'app/services/permissions.service';

@Component({
  selector: 'app-staff-logs',
  templateUrl: './staff-logs.component.html',
  styleUrls: ['./staff-logs.component.scss']
})
export class StaffLogsComponent implements OnInit {

  searchText
  users = [
    { name: 'APP Status', text: 'To administer the system and creating roster & shifts.', url: 'app-status', icon: 'verified_user', image: '/assets/images/nav-images/adminpic.jpg', enabled: false },
    { name: 'Time Clock', text: 'To whom we provide our services for guarding their sites.', url: 'time-clock', icon: 'supervised_user_circle', image: '/assets/images/nav-images/customers.jpg', enabled: false },
    { name: 'Internal Staff Activity Log', text: 'Third party who provide Staff and their services to our company.', url: 'activity-log', icon: 'supervised_user_circle', image: '/assets/images/nav-images/contractor.jpg', enabled: false },
  ]
  adminPermissions
  constructor(private permissionService: PermissionsService, private router: Router) {
    this.adminPermissions = this.permissionService.getPermissionsByTitle('Staff Logs');
    for (const user of this.users) {
      const matchingChild = this.adminPermissions?.childPage?.find(child => child.title === user.name);
      if (matchingChild) {
        user.enabled = matchingChild.enabled;
      } else {
        user.enabled = false;
      }
    }
  }

  ngOnInit(): void {

  }

  redirect(userType: string) {
    this.router.navigate([userType]);
  }

}
