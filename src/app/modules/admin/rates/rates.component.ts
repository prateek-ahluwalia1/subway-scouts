import { Component, OnInit } from '@angular/core';
import { Router } from '@angular/router';
import { PermissionsService } from 'app/services/permissions.service';
import { TrackAdminActivityService } from 'app/services/track-admin-activity.service';

@Component({
  selector: 'app-rates',
  templateUrl: './rates.component.html',
  styles:[
    `
    .common-style-for-cards .main .card {
      height:380px !important;
    }
    `
  ],
})
export class RatesComponent implements OnInit {

  searchText
  routeId
  adminPermission: any
  invoice: any
  constructor(public router: Router, private trackAdmin: TrackAdminActivityService,
    private permissionService: PermissionsService) {
    this.adminPermission = this.permissionService.getPermissionsByTitle('Accounts');
    this.invoice = this.adminPermission?.childPage?.find(item => item.title === 'Invoice');
    for (const user of this.rates) {
      const matchingChild = this.adminPermission?.childPage?.find(child => child.title === user.name);
      if (matchingChild) {
        user.enabled = matchingChild.enabled;
      } else {
        user.enabled = false; // Set a default value, e.g., false
      }
    }
    if (this.adminPermission && this.adminPermission.actions) {
      this.adminPermission.actionsObject = {};
      for (const action of this.adminPermission.actions) {
        this.adminPermission.actionsObject[action.title] = action.enabled;
      }
    }
    console.log(this.invoice);
    let routerId = localStorage.getItem('routerId')
    if (!routerId) {
      this.activity()
    }
  }

  rates = [
    { name: 'Charge Rates', text: 'The amount which is charged from the customer.', url: 'charge-rate', icon: 'heroicons_outline:currency-dollar', image: '/assets/images/nav-images/chargeRates.png', enabled: false },
    { name: 'Pay Rates', text: 'The amount which is paid to the staff.', url: 'pay-rate', icon: 'heroicons_outline:currency-dollar', image: '/assets/images/nav-images/payrates.png', enabled: false },
  ]

  ngOnInit(): void {


  }

  ngOnDestroy() {
    // localStorage.removeItem('routerId');

    // this.trackAdmin.storeActivity('Exit Rates Page', 'Exit Rates Page', this.routeId).subscribe(res => {
    // })
  }

  activity() {
    this.trackAdmin.storeActivity('Rates Page', 'Enter in Rates Page').subscribe(({ success, id }) => {
      if (success) {
        localStorage.setItem('routerId', id)
        this.routeId = id
      }
    })
  }

  Rates(communiType: string) {
    this.router.navigate(['/rates', communiType]);
  }

}
