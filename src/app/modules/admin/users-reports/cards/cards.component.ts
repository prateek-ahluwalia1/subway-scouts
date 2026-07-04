import { ChangeDetectionStrategy, Component, OnInit } from '@angular/core';
import { Router } from '@angular/router';
import { trigger, transition, style, animate, query, stagger } from '@angular/animations';
import { TrackAdminActivityService } from 'app/services/track-admin-activity.service';
import { PermissionsService } from 'app/services/permissions.service';

@Component({
  selector: 'app-cards',
  templateUrl: './cards.component.html',
  styles:[
    `
    .common-style-for-cards .main .card {
      height:380px !important;
    }
    `
  ],
  changeDetection: ChangeDetectionStrategy.OnPush,
  animations: [
    trigger('fadeIn', [
      transition(':enter', [
        style({ opacity: 0 }),
        animate('300ms', style({ opacity: 1 }))
      ])
    ]),
    trigger('stagger', [
      transition('* => *', [
        query(':enter', [
          style({ opacity: 0, transform: 'translateY(-15px)' }),
          stagger(100, [
            animate('300ms', style({ opacity: 1, transform: 'none' }))
          ])
        ], { optional: true })
      ])
    ])
  ]
})
export class CardsComponent implements OnInit {

  searchText
  routeId
  tabs: any = ['All', 'WFM Reports', 'CRM Reports', 'Company Reports']
  icons: string[] = ['icon-bolt', 'icon-calendar','icon-file','icon-certificate'];

  selectedTab: string = 'All';
  newArray: any[] = []

  constructor(public router: Router, private trackAdmin: TrackAdminActivityService, private permissionService: PermissionsService) {
    this.adminPermissions = this.permissionService.getPermissionsByTitle('Reports');
    console.log("All Permissions", this.adminPermissions)
    for (const user of this.reports) {
      const matchingChild = this.adminPermissions?.childPage?.find(child => child.title === user.name);
      if (matchingChild) {
        user.enabled = matchingChild.enabled;
      } else {
        user.enabled = false;
      }
    }
    let routerId = localStorage.getItem('routerId')
    if (!routerId) {
      this.activity()
    }
  }

  adminPermissions: any

  reports = [
    // { name: 'Task Report', text: 'Used to display report of tasks assigned to a specific staff and its detail.', url: 'task-report', icon: 'bar_chart', image: '/assets/images/nav-images/taskreport.jpg  ', enabled: false },
    { name: 'Staff Report', text: ' Displays data of the staff profile.', url: 'staff-report', icon: 'bar_chart', image: '/assets/images/nav-images/staffreport.jpg', enabled: false },
    // { name: 'Invoice Report', text: 'Displays data of the amount which is charged from the customer.', url: 'invoice', icon: 'bar_chart', image: '/assets/images/nav-images/invoicereport.png', enabled: false },
    // { name: 'Incident Report', text: 'Used for fetching data of any incident reported by the staff on their shift.', url: 'incident-report', icon: 'accessible', image: '/assets/images/nav-images/incidentreport.png', enabled: false },
    // { name: 'Employee Training Matrix', text: 'Used to display staff data ', url: 'employee-training-matrix', icon: 'bubble_chart', image: '/assets/images/nav-images/multiplereport.jpg', enabled: false },
    // { name: 'SignIn-Out Report', text: 'Shows data of the staff that when did he sign in and out during their shift.', url: 'sign-in-out-report', icon: 'bar_chart', image: '/assets/images/nav-images/signinoutreport.jpg', enabled: false },
    // { name: 'Quick Paysheet', text: ' Display concise data of the staff and their total pay as per selected date range. ', url: 'quick-paysheet', icon: 'monetization_on', image: '/assets/images/nav-images/quickpaysheet.png', enabled: false },
    // { name: 'Payroll Paysheet', text: 'Provides a detailed view of the staff salary and hourly wage rate.', url: 'payroll-paysheet', icon: 'bar_chart', image: '/assets/images/nav-images/completepaysheet.png', enabled: false },
    { name: 'Complete Paysheet', text: 'Provides a detailed view of the staff salary and hourly wage rate.', url: 'complete-paysheet', icon: 'bar_chart', image: '/assets/images/nav-images/completepaysheet.png', enabled: false },
    // { name: 'Old Complete Paysheet', text: 'Provides a detailed view of the staff salary and hourly wage rate.', url: 'old-complete-paysheet', icon: 'bar_chart', image: '/assets/images/nav-images/completepaysheet.png', enabled: false },
    // { name: 'Adhoc/Permanent Report', text: ' Used to display report of shifts assigned to a specific staff and its detail.', url: 'adhoc-hours-shift-report', icon: 'bar_chart', image: '/assets/images/nav-images/staffreport.jpg', enabled: false },
    // { name: '12 Hours Shift Report', text: ' Used to display report of shifts assigned to a specific staff and its detail.', url: 'twelve-hours-shift-report', icon: 'bar_chart', image: '/assets/images/nav-images/staffreport.jpg', enabled: false },
    // { name: 'Midnight Hours Report', text: 'Used to display monthly hours report ', url: 'midnight-hours-report', icon: 'bubble_chart', image: '/assets/images/nav-images/multiplereport.jpg', enabled: false },
    // { name: '40 Hours Shift Report', text: 'Used to display report of shifts assigned to a specific staff and its detail.', url: 'fourty-hours-shift-report', icon: 'bar_chart', image: '/assets/images/nav-images/taskreport.jpg  ', enabled: false },
    // { name: '10 Hours Shift Report', text: ' Used to display report of shifts assigned to a specific staff and its detail.', url: 'ten-hours-shift-report', icon: 'bar_chart', image: '/assets/images/nav-images/staffreport.jpg', enabled: false },
    // { name: '36 Hours Shift Report', text: ' Used to display report of shifts assigned to a specific staff and its detail.', url: 'thirtysix-hours-shift-report', icon: 'bar_chart', image: '/assets/images/nav-images/staffreport.jpg', enabled: false },
    // { name: 'Multi Report', text: 'Merge the data of all the other reports into a single report.', url: 'multi-report', icon: 'bubble_chart', image: '/assets/images/nav-images/multiplereport.jpg', enabled: false },
    // { name: 'Monthly Report', text: 'Merge the data of all the other reports into a single report.', url: 'monthly-report', icon: 'bubble_chart', image: '/assets/images/nav-images/multiplereport.jpg', enabled: false },
    // { name: 'Admin Log', text: 'To track the activity of your own task as well as other admins.', url: 'admin-log', icon: 'bar_chart', image: '/assets/images/nav-images/tasklog.png', enabled: false },
    { name: 'Time Sheet', text: 'Used to display the detailed overview of staff work hours.', url: 'time-sheet', icon: 'bar_chart', image: '/assets/images/nav-images/timesheetreport.png', enabled: false },
    // { name: 'Job Tracker', text: 'Presents a detailed summary of employee work hours.', url: 'job-tracker', icon: 'bar_chart', image: '/assets/images/nav-images/timesheetreport.png', enabled: false },
    // { name: 'Green & Welfare Call', text: 'Used to display the detailed overview of staff work hours.', url: 'green-welfare-call', icon: 'bar_chart', image: '/assets/images/nav-images/timesheetreport.png', enabled: false },
    // { name: 'Audit Report', text: 'Merge the data of all the other reports into a single report.', url: 'audit-report', icon: 'bubble_chart', image: '/assets/images/nav-images/multiplereport.jpg', enabled: false },
    // { name: 'Profit & Loss Report', text: 'Merge the data of all the other reports into a single report.', url: 'profit-loss-report', icon: 'bubble_chart', image: '/assets/images/nav-images/multiplereport.jpg', enabled: false },
    // { name: 'Leave Report', text: 'Used to display leave history', url: 'leave-report', icon: 'bubble_chart', image: '/assets/images/nav-images/multiplereport.jpg', enabled: false },
    // { name: 'CRM Overview Report', text: 'Used to display crm ', url: 'crm-report', icon: 'bubble_chart', image: '/assets/images/nav-images/multiplereport.jpg', enabled: false },
    // { name: 'CRM Customer Report', text: 'Used to display CRM customer report ', url: 'crm-customer', icon: 'bubble_chart', image: '/assets/images/nav-images/multiplereport.jpg', enabled: false },
    // { name: 'Monthly PnL Report', text: 'Used to display Month wise report ', url: 'pnl-report', icon: 'bubble_chart', image: '/assets/images/nav-images/multiplereport.jpg', enabled: false },
    // { name: 'Guest Auth Report', text: 'Used to display auth staff data ', url: 'guest-auth-report', icon: 'bubble_chart', image: '/assets/images/nav-images/multiplereport.jpg', enabled: false },
    // { name: 'Comparison Report', text: 'Used to display comparison ', url: 'comparison-report', icon: 'bubble_chart', image: '/assets/images/nav-images/multiplereport.jpg', enabled: false },
  ]

  ngOnInit(): void {
    this.getTabData(this.selectedTab)
  }

  ngOnDestroy() {
    // this.trackAdmin.storeActivity('Exit Reports Page', 'Exit Reports Page', this.routeId).subscribe(res => {
    // })
    // localStorage.removeItem('routerId');
  }

  activity() {
    this.trackAdmin.storeActivity('Reports Page', 'Enter in Reports Page').subscribe(({ success, id }) => {
      if (success) {
        localStorage.setItem('routerId', id)
        this.routeId = id
      }
    })
  }

  jobTracker(userType: string, name) {
    if (name == 'Admin Log') {
      this.router.navigate([userType]);
    }
    else if (name == 'Time Sheet') {
      this.router.navigate([userType]);
    }
    else {
      this.router.navigate(['/reports', userType]);
    }
  }

  getTabData(type) {
    if (type == 'All') {
      this.newArray = this.reports;
    }
    // else if (type === 'WFM Reports') {
    //   this.newArray = this.reports.filter(item =>
    //     item.name === 'Task Report' || item.name === 'Staff Report' || item.name === 'Invoice Report' || item.name === 'Incident Report'
    //     || item.name === 'SignIn-Out Report' || item.name === 'Quick Paysheet' || item.name === 'Complete Paysheet' || item.name === 'Multi Report'
    //     || item.name === 'Admin Log' || item.name === 'Time Sheet' || item.name === 'Green & Welfare Call' || item.name === 'Audit Report'
    //     || item.name === 'Profit & Loss Report' || item.name === 'Leave Report' || item.name === 'Midnight Hours Report' || item.name === '40 Hours Shift Report'
    //     || item.name === '10 Hours Shift Report' || item.name === 'Monthyly Report' || item.name === 'Adhoc/Permanent Report'
    //   );
    // }
    // else if (type === 'Company Reports') {
    //   this.newArray = this.reports.filter(item =>
    //     item.name === 'Monthyly Report'
    //   );
    // }
    // else if (type === 'CRM Reports') {
    //   this.newArray = this.reports.filter(item =>
    //     item.name === 'CRM Overview Report' || item.name === 'CRM Customer Report' || item.name === 'Monthly PnL Report'
    //   );
    // } 
    else {
      this.newArray = this.reports.filter(item => item.name === type);
    }
  }
}
