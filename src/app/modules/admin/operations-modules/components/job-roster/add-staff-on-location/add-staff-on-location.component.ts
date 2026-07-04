import { Component, Input, OnInit } from '@angular/core';
import { ThemePalette } from '@angular/material/core';
import { NgbModal } from '@ng-bootstrap/ng-bootstrap';
import { JobRoster1Service } from 'app/services/job-roster1.service';
import { StaffService } from 'app/services/staff.service';
import { ToastServiceService } from 'app/services/toast-service.service';
export class Customers {
  id: number;
  name: string;
}
@Component({
  selector: 'app-add-staff-on-location',
  templateUrl: './add-staff-on-location.component.html',
})
export class AddStaffOnLocationComponent implements OnInit {
  @Input() userType;
  @Input() customers: Customers[] = [];
  fromMultiCustomer: any;
  customersIds: any[] = [];
  searchlocation
  color: ThemePalette = 'accent';
  checkedSlide = false;
  disabledSlide = false;
  searchTerm
  constructor(public userService: StaffService, private rosterService: JobRoster1Service, private modalService: NgbModal,
    private toast: ToastServiceService) { }

  ngOnInit(): void {
  }


  // fetch data from child
  singleSites
  customersId
  selectedCus(data: string) {
    this.fromMultiCustomer = data;
    this.customersId = this.fromMultiCustomer.value.id;
    const array = [this.customersId];
    if (this.fromMultiCustomer) {
      this.userService.getCusSite(array).subscribe(res => {
        if (res.success) {
          this.singleSites = res.data
        }
      })
    }
  }


  singleSite
  receiveDataFromChildSi(data: string) {
    this.fromMultiCustomer = data;
    this.singleSite = this.fromMultiCustomer.value.id
    if (this.singleSite) {
      this.getGuadSiteCustomer()
    }
  }

  customers_guards
  site_guards
  getGuadSiteCustomer() {
    this.rosterService.getGuadSiteCustomer(this.singleSite, this.customersIds).subscribe(({ customers_guards, success, site_guards }) => {
      if (success) {
        this.site_guards = site_guards
        this.customers_guards = customers_guards
      }
      else {
        this.site_guards = []
        this.customers_guards = []
      }
    })
  }

  close(data?) {
    this.modalService.dismissAll(data);
  }



  toggleGuard(event, guard?, action?) {
    let guard_ids
    if (action && action == 'add_to_site_at_once') {
      guard_ids = this.customers_guards ? this.customers_guards.map(id => id.id) : ''
      if (!event.checked) {
        action = 'remove_to_site_at_once'
        guard_ids = this.site_guards ? this.site_guards.map(id => id.id) : ''

      }
      this.rosterService.addGuard(guard_ids, this.singleSite, action).subscribe(({ success, msg }) => {
        if (success) {
          if (this.singleSite) {
            this.getGuadSiteCustomer()
          }
          this.toast.toastNotification(msg, 'Add Staff On Site')
        }
      })
    }
    else {
      this.rosterService.addGuard(guard.id, this.singleSite, action).subscribe(({ success, msg }) => {
        if (success) {
          if (this.singleSite) {
            this.getGuadSiteCustomer()
          }
          this.toast.toastNotification(msg, 'Add Staff On Site')
        }
      })
    }
  }

  onTabChange(event) {
    const selectedTabIndex = event.index
    if (selectedTabIndex === 1) {
      this.checkedSlide = true
    } else {
      this.checkedSlide = false
    }
  }
}
