import { Component, OnInit, ViewChild } from '@angular/core';
import { Router } from '@angular/router';
import { TrackAdminActivityService } from 'app/services/track-admin-activity.service';
import { PersonDetailComponent } from '../models/person-detail/person-detail.component';
import { ModalDismissReasons, NgbModal } from '@ng-bootstrap/ng-bootstrap';
import { OnBoardingStaffComponent } from '../models/on-boarding-staff/on-boarding-staff.component';
import { CreateAdminComponent } from '../models/create-admin/create-admin.component';
import { GlobalVariable } from 'app/shared/global';
import { CreateCustomerComponent } from '../component/create-customer/create-customer.component';
import { ToastServiceService } from 'app/services/toast-service.service';
import { MatMenuTrigger } from '@angular/material/menu';
import { PermissionsService } from 'app/services/permissions.service';

@Component({
  selector: 'app-all-users',
  templateUrl: './all-users.component.html',
  styleUrls: ['./all-users.component.scss']
})
export class AllUsersComponent implements OnInit {

  searchText
  new_staff_dropdown = false;
  @ViewChild(MatMenuTrigger) trigger: MatMenuTrigger;
  // this array name should be matched with data.ts navigation childPage Object
  users = [
    // { name: 'Admins', text: 'To administer the system and creating roster & shifts.', url: 'admins', icon: 'verified_user', image: '/assets/images/nav-images/admin.png', enabled: false },
    // { name: 'Customers', text: 'To whom we provide our services for guarding their sites.', url: 'customers', icon: 'supervised_user_circle', image: '/assets/images/nav-images/customers.png', enabled: false },
    // { name: 'Contractors', text: 'Third party who provide Staff and their services to our company.', url: 'contractor', icon: 'supervised_user_circle', image: '/assets/images/nav-images/contractors.png', enabled: false },
    // { name: 'Sales Person', text: 'A list of individuals who actively engage in promoting the system.', url: 'sales-person', icon: 'verified_user', image: '/assets/images/nav-images/sales person.png', enabled: false },
    { name: 'Other Staff', text: 'Displays data of the staff who are currently on board', url: 'staff', icon: 'supervised_user_circle', image: '/assets/images/nav-images/other staff.png', enabled: false },
    // { name: 'Potential Staff', text: 'Displays data of the staff who sign up through the app.', url: 'potential-new-staff', icon: 'supervised_user_circle', image: '/assets/images/nav-images/potential staff.png', enabled: false },
    // { name: 'File Manager', text: 'Organise, manage, and access users files efficiently and quickly.', url: 'file-manager', icon: 'supervised_user_circle', image: '/assets/images/nav-images/File manager.png', enabled: false },
    // { name: 'Patrol Car Details', text: '', url: 'patrol-car-details', icon: 'supervised_user_circle', image: '/assets/images/nav-images/File manager.png', enabled: false },
  ]

  routeId
  rosterPermissions: any;
  admin
  customer
  contractor
  curStaff
  salePerson

  constructor(public router: Router, private trackAdmin: TrackAdminActivityService,
    private modalService: NgbModal, private global: GlobalVariable, private toast: ToastServiceService,
    private permissionService: PermissionsService) {
    this.rosterPermissions = this.permissionService.getPermissionsByTitle('Onboarding');

    for (const user of this.users) {
      const matchingChild = this.rosterPermissions?.childPage?.find(child => child.title === user.name);
      user.enabled = matchingChild ? matchingChild.enabled : false;
    }

    this.admin = this.findChildPage('Admins');
    this.salePerson = this.findChildPage('Sales Person');
    this.customer = this.findChildPage('Customers');
    this.contractor = this.findChildPage('Contractors');
    this.curStaff = this.findChildPage('Other Staff');

    this.initActivityTracking();
  }

  private findChildPage(title: string) {
    return this.rosterPermissions?.childPage?.find(item => item.title === title);
  }

  private initActivityTracking() {
    let routerId = localStorage.getItem('routerId');
    if (!routerId) {
      this.activity();
    }
  }

  ngOnDestroy() {
    // localStorage.removeItem('routerId');

    // this.trackAdmin.storeActivity('Exit System Users Page', 'Exit System Users Page', this.routeId).subscribe(res => {
    // })
  }

  private activity() {
    this.trackAdmin.storeActivity('System Users Page', 'Enter in System Users Page').subscribe(({ success, id }) => {
      if (success) {
        localStorage.setItem('routerId', id);
        this.routeId = id;
      }
    });
  }

  ngOnInit(): void {

  }

  /**Add new Staff Model */
  addNewStaff() {
    this.new_staff_dropdown = !this.new_staff_dropdown;
  }

  createNewStaff(staff) {
    this.new_staff_dropdown = false;
    if (staff == "newStaff") {
      const modalRef = this.modalService.open(PersonDetailComponent, {
        windowClass: "personalDetailOpenModalClass ",
        size: "xl",
        animation: false,
      });
      modalRef.componentInstance.fromParent = staff;
      modalRef.result.then(
        (result) => {
          console.log("Modal Result", `Closed with: ${result}`);
        },
        (reason) => {
          let status = this.getDismissReason(reason);
          console.log(status);
        }
      );
    } else {
      const modalRef = this.modalService.open(OnBoardingStaffComponent, {
        centered: true,
        size: "lg",
        windowClass: "onboarding-model",
        animation: true,
      });
      modalRef.componentInstance.fromParent = staff;
      modalRef.result.then(
        (result) => {
          console.log("Modal Result", `Closed with: ${result}`);
        },
        (reason) => {
          let status = this.getDismissReason(reason);
          if (status == "QuickStaff") {
          }
        }
      );
    }
  }

  getDismissReason(reason: any): string {
    if (reason === ModalDismissReasons.ESC) {
      return "by pressing ESC";
    } else if (reason === ModalDismissReasons.BACKDROP_CLICK) {
      return "by clicking on a backdrop";
    } else {
      return `${reason}`;
    }
  }

  jobTracker(userType: string) {
    const path = `users/${userType}`;
    this.router.navigate([path]);
  }

  creatNow(url) {
    if (url == 'admins' || url == 'sales-person') {
      const isSalesPersonSection = url === 'sales-person';
      const modelRef = this.modalService.open(CreateAdminComponent, { windowClass: "create-admin", size: 'lg' })
      modelRef.componentInstance.fromParent = 'new';
      modelRef.componentInstance.fromSalesPersonSection = isSalesPersonSection;
      modelRef.result.then((result) => {
        console.log("Modal Result", `Closed with: ${result}`)
      }, (reason) => {
        this.global.selectedCustomers = []
        console.log("Modal Closed Reason -> ", `Dismissed ${this.getDismissReason(reason)}`)

      });
    }
    else if (url == 'customers') {
      const modalRef = this.modalService.open(CreateCustomerComponent, {
        size: 'lg',
      })
      modalRef.componentInstance.fromParent = 'createCustomer';
      modalRef.result.then((result) => {
        console.log("Modal Result", `Closed with: ${result}`)
      }, (reason) => {
        if (reason == 'create') {
          this.toast.toastNotification('Customer Created Successfully', 'Customer Operation!')
        }
      });
    }
    else if (url == 'contractor') {
      const modalRef = this.modalService.open(CreateCustomerComponent, {
        size: 'lg',
      })
      modalRef.componentInstance.fromParent = 'createContractor';
      modalRef.result.then((result) => {
        console.log("Modal Result", `Closed with: ${result}`)
      }, (reason) => {
        if (reason == 'create') {
          this.toast.toastNotification('Contractor Created Successfully', 'Contractor Operation!')
        }
      });
    }
    else if (url == 'staff') {
      this.trigger.openMenu();
    }
  }

}
