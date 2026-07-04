import { SelectionModel } from '@angular/cdk/collections';
import { Component, OnDestroy, OnInit } from '@angular/core';
import { MatDialog } from '@angular/material/dialog';
import { MatTableDataSource } from '@angular/material/table';
import { ModalDismissReasons, NgbModal, NgbModalRef } from '@ng-bootstrap/ng-bootstrap';
import { ContractorService } from 'app/services/contractor.service';
import { CreateCustomerComponent } from '../component/create-customer/create-customer.component';
import { DialogboxComponent } from '../dialogbox/dialogbox.component';
import { ToastServiceService } from 'app/services/toast-service.service';
import { CustomerMainComponent } from '../models/customer-main/customer-main.component';
import { TrackAdminActivityService } from 'app/services/track-admin-activity.service';
import { GlobalVariable } from 'app/shared/global';
import { PermissionsService } from 'app/services/permissions.service';

export interface PeriodicElement {}
const ELEMENT_DATA: PeriodicElement[] = [];

@Component({
  selector: 'app-contractor',
  templateUrl: './contractor.component.html',
  styleUrls: ['./contractor.component.scss']
})

export class ContractorComponent implements OnInit, OnDestroy {

  adminList = []
  displayedColumns: string[] = ['name', 'email', 'status', 'address', 'action'];
  dataSource = new MatTableDataSource<PeriodicElement>(ELEMENT_DATA);
  selection = new SelectionModel<PeriodicElement>(true, []);
  closeAdminCreateModal: NgbModalRef;
  userType = 'active';
  idleState = 'Not started.';
  inactive_customers
  active_customers
  searchTerm
  contPermissions
  id;
  type;
  data;
  routeId

  constructor(public dialog: MatDialog, public model: NgbModal, private server: ContractorService,
    private toast: ToastServiceService, private trackAdmin: TrackAdminActivityService, private global: GlobalVariable,
    private permissionService: PermissionsService) {

    this.getContractor_data();
    let routerId = localStorage.getItem('routerId')
    if (!routerId) {
      this.activity()
    }
  }

  ngOnInit(): void {

    const per = this.permissionService.getPermissionsByTitle('Onboarding');
    this.contPermissions = per?.childPage?.find(item => item.title === 'Contractors');
    console.log(this.contPermissions);

    this.dataSource.data = this.adminList;
  }

  search() {
    if (!this.searchTerm || this.searchTerm.length < 3) {
      this.dataSource.data = this.adminList;
      return;
    }
    const results = this.adminList.filter((item) => {
      const nameMatch = item.name.toLowerCase().includes(this.searchTerm.toLowerCase());
      const emailMatch = item.email.toLowerCase().includes(this.searchTerm.toLowerCase());
      const addressMatch = item.address.toLowerCase().includes(this.searchTerm.toLowerCase());
      return nameMatch || emailMatch || addressMatch;
    });

    this.dataSource.data = results.length > 0 ? results : [];
  }

  getContractor_data() {
    let params = {
      status: this.userType,
    }
    this.server.getContractor(params).subscribe(({ success, data, active_customers, inactive_customers }) => {
      if (success) {
        this.adminList = data;
        this.dataSource.data = this.adminList;
        this.dataSource.data = this.adminList;
        this.inactive_customers = inactive_customers
        this.active_customers = active_customers
      }
    });
  }

  getUserType(userType: string) {
    this.searchTerm = ''
    this.userType = userType;
    this.getContractor_data()
  }

  getDismissReason(reason: any): string {
    if (reason === ModalDismissReasons.ESC) {
      return 'by pressing ESC';
    } else if (reason === ModalDismissReasons.BACKDROP_CLICK) {
      return 'by clicking on a backdrop';
    } else {
      return `with: ${reason}`;
    }
  }

  createContractor() {
    const modalRef = this.model.open(CreateCustomerComponent, { size: 'lg',})
    modalRef.componentInstance.fromParent = 'createContractor';
    modalRef.result.then((result) => {
      console.log("Modal Result", `Closed with: ${result}`)
    }, (reason) => {
      if (reason == 'create') {
        this.toast.toastNotification('Contractor Created Successfully', 'Contractor Operation!')
        this.getContractor_data()
      }
    });
  }

  openModel(id) {
    this.server.getSpecContractor(id).subscribe(res => {
      if (res.success) {
        const modalRef = this.model.open(CustomerMainComponent, { windowClass: "customerMain", size: 'xl', scrollable: true })
        modalRef.componentInstance.fromParent = res;
        modalRef.componentInstance.type = 'contractor';
        modalRef.result.then((result) => {
          console.log("Modal Result", `Closed with: ${result}`)
        }, (reason) => {
          console.log(reason);
          if (reason == 'true') {
            this.toast.toastNotification('Contractor Updated Successfully!', 'Contractor Operation!');
          }
          if (reason == 'update') {
            console.log(reason);
            this.getContractor_data()
          }
        });
      }
    }, (error) => {
      console.log(error);
    });
  }

  delContractor(id) {
    console.log(id);
    this.dialog.open(DialogboxComponent, {
      width: '300px',
    }).afterClosed().subscribe(response => {
      if (response == 'yes') {
        this.server.delContractor(id).subscribe(({ success, message }) => {
          if (success) {
            this.toast.toastNotification1(message, 'Contractor Operation!')
            this.getContractor_data()
          }
        }, (error) => {
          console.log(error);
        });
      }
    });
  }

  updateActiveStatus(element) {
    let data = {
      id: element.id,
      admin_id: this.global.admin.admin_id
    }
    this.server.change_ContractorStatus(data).subscribe(({ msg, success }) => {
      if (success) {
        let status = 'Contractor Operation!';
        this.toast.toastNotification(msg, status)
        this.getContractor_data()
      }
    });
  }

  activity() {
    this.trackAdmin.storeActivity('Administrator', `Enter in Administrator Page`).subscribe(({ success, id }) => {
      if (success) {
        localStorage.setItem('routerId', id)
      }
    })
  }

  ngOnDestroy() {
    this.trackAdmin.storeActivity('Administrator', 'Exit Administrator Page', localStorage.getItem('routerId')).subscribe(res => {
      if (res.success) {
        localStorage.removeItem('routerId');
      }
    })
  }

}
