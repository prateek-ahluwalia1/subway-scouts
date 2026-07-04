import { Component, OnInit } from '@angular/core';
import { Router } from '@angular/router';
import { PermissionsService } from 'app/services/permissions.service';

@Component({
  selector: 'app-operations',
  templateUrl: './operations.component.html',
  styleUrls: ['./operations.component.scss']
})

export class OperationsComponent implements OnInit {

  // tabs: any = ['All', 'Staff Roster/Scheduling', 'Locations', 'Staff Documents', 'Visa/Vivo Check', 'Leave Management', 'Staff Logs', 'Patrolling Roster'];
  // tabs: any = ['All', 'Staff Roster/Scheduling', 'Locations', 'Staff Documents', 'Visa/Vivo Check', 'Leave Management', 'Staff Logs'];
  tabs: any = ['All', 'Staff Roster/Scheduling', 'Branches'];
  icons: string[] = ['icon-bolt', 'icon-calendar', 'icon-map-marker', 'icon-file', 'icon-check', 'icon-tasks', 'icon-file', 'icon-calendar'];
  newArray: any[] = [];
  selectedTab: string = 'All';
  searchText: string;
  adminPermissions: any;

  users = [
    { name: 'Staff Roster/Scheduling', text: 'To administer the system and creating roster & shifts.', url: 'roster-type', icon: 'verified_user', image: '/assets/images/nav-images/Roster.png', enabled: false },
    { name: 'Branches', text: 'These are the branches where the staff performs their duties.', url: 'location', icon: 'supervised_user_circle', image: '/assets/images/nav-images/location.png', enabled: false },
    // { name: 'Staff Documents', text: 'All the staff documents are in one place. Accessible and categorised quickly.', url: 'staff-documentation', icon: 'supervised_user_circle', image: '/assets/images/nav-images/staff_documents.png', enabled: false },
    // { name: 'New Entry/Query', text: 'To input the visa information of a staff member.', url: 'check-visa-status', icon: 'heroicons_outline:chat-alt', image: '/assets/images/nav-images/new_entry.png', enabled: false },
    // { name: 'Visa Status', text: 'To check the visa status of a staff.', url: 'check-visa-status-auto', icon: 'heroicons_outline:chat-alt', image: '/assets/images/nav-images/visa_status.png', enabled: false },
    // { name: 'Leave Management', text: 'Keep up to date with the staffs day off without any delay.', url: 'leave-management', icon: 'verified_user', image: '/assets/images/nav-images/leave_management.png', enabled: false },
    // { name: 'APP Status', text: 'To track staff app status accurately and in real time.', url: 'app-status', icon: 'verified_user', image: '/assets/images/nav-images/Appstatus.png', enabled: false },
    // { name: 'Time Clock', text: 'Record hours worked by staff on each site to keep time sheets error-free.', url: 'time-clock', icon: 'supervised_user_circle', image: '/assets/images/nav-images/timeclock.png', enabled: false },
    // { name: 'Internal Staff Activity Log', text: 'It is used to track the activity of other users.', url: 'activity-log', icon: 'supervised_user_circle', image: '/assets/images/nav-images/internal_staff.png', enabled: false },
    // { name: 'Runsheet', text: 'Key areas assigned to staff for task execution.', url: 'runsheet', icon: 'supervised_user_circle', image: '/assets/images/nav-images/runsheet.png', enabled: false },
    // { name: 'Patrol Roster', text: 'To create a roster and shifts for run sheets.', url: 'runsheet-roster', icon: 'supervised_user_circle', image: '/assets/images/nav-images/runsheet_roster.png', enabled: false },
    // { name: 'Alarm Dispatch System', text: '', url: 'alarm-dispatch-system', icon: 'supervised_user_circle', image: '/assets/images/nav-images/runsheet_roster.png', enabled: false },
  ];

  constructor(private permissionService: PermissionsService, public router: Router) {
    this.adminPermissions = this.permissionService.getPermissionsByTitle('WFM Tools');
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
    this.getTabData(this.selectedTab);
  }

  getTabData(type: string) {
    if (type === 'All') {
      this.newArray = this.users;
    } else if (type === 'Patrolling Roster') {
      // this.newArray = this.users.filter(item =>
      //   item.name === 'Runsheet' || item.name === 'Patrol Roster' || item.name === 'Alarm Dispatch System'
      // );
    } else if (type === 'Visa/Vivo Check') {
      this.newArray = this.users.filter(item =>
        item.name === 'Visa Status' || item.name === 'New Entry/Query'
      );
    } else if (type === 'Staff Logs') {
      this.newArray = this.users.filter(item =>
        item.name === 'APP Status' || item.name === 'Time Clock' || item.name === 'Internal Staff Activity Log'
      );
    } else {
      this.newArray = this.users.filter(item => item.name === type);
    }
  }

  redirect(url: string, name: string) {
    console.log(`Navigating to ${name}`);
    this.router.navigate([`/operations/${url}`]);
  }

}
