import { SelectionModel } from '@angular/cdk/collections';
import { Component, OnDestroy, OnInit, ViewChild } from '@angular/core';
import { MatDialog } from '@angular/material/dialog';
import { MatTableDataSource } from '@angular/material/table';
import { NgbModal, NgbModalRef, ModalDismissReasons } from '@ng-bootstrap/ng-bootstrap';
import { CustomerService } from 'app/services/customer.service';
import { CreateCustomerComponent } from '../component/create-customer/create-customer.component';
import { DialogboxComponent } from '../dialogbox/dialogbox.component';
import { CustomerMainComponent } from '../models/customer-main/customer-main.component';
import { ToastServiceService } from 'app/services/toast-service.service';
import { PermissionsService } from 'app/services/permissions.service';
import { GlobalVariable } from 'app/shared/global';
import { TrackAdminActivityService } from 'app/services/track-admin-activity.service';
import { MatPaginator } from '@angular/material/paginator';

export interface PeriodicElement { }

@Component({
  selector: 'app-customer',
  templateUrl: './customer.component.html',
})

export class CustomerComponent implements OnInit, OnDestroy {

  adminList: any[] = [];
  displayedColumns: string[] = ['name', 'email', 'status', 'address', 'action'];
  dataSource = new MatTableDataSource<PeriodicElement>([]);
  selection = new SelectionModel<PeriodicElement>(true, []);
  closeAdminCreateModal: NgbModalRef;
  userType = 'active';
  searchTerm: string = '';
  custPermissions: any;
  active: any;
  inactive: any;
  @ViewChild(MatPaginator) paginator: MatPaginator;

  constructor(
    public dialog: MatDialog,
    public model: NgbModal,
    private cusservice: CustomerService,
    private toast: ToastServiceService,
    private permissionService: PermissionsService,
    private global: GlobalVariable,
    private trackAdmin: TrackAdminActivityService
  ) {
    this.getCus_data();
  }

  ngOnInit(): void {
    const per = this.permissionService.getPermissionsByTitle('Onboarding');
    this.custPermissions = per?.childPage?.find((item) => item.title === 'Customers');
    let routerId = localStorage.getItem('routerId')
    if (!routerId) {
      this.activity()
    }
  }

  openModel(id: number) {
    this.cusservice.getSpecCustomer(id).subscribe(
      (res) => {
        if (res.success) {
          const modalRef = this.model.open(CustomerMainComponent, {
            windowClass: 'customerMain',
            size: 'xl',
            scrollable: true,
          });
          modalRef.componentInstance.fromParent = res;
          modalRef.componentInstance.type = 'customer';
          modalRef.result.then(
            (result) => {
              console.log('Modal Result', `Closed with: ${result}`);
            },
            (reason) => {
              if (reason == 'true') {
                this.toast.toastNotification('Customer Updated Successfully!', 'Customer Operation!');
              }
              if (reason == 'update') {
                this.getCus_data();
              }
            }
          );
        }
      },
      (error) => {
        console.log(error);
      }
    );
  }

  getCus_data() {
    let params = {
      status: this.userType,
    };
    this.cusservice.getCustomer(params).subscribe(({ success, data, inactive_customers, active_customers }) => {
      if (success) {
        this.adminList = data;
        this.dataSource = new MatTableDataSource(this.adminList);
        this.dataSource.paginator = this.paginator;
        this.active = active_customers;
        this.inactive = inactive_customers;
      }
    });
  }

  getUserType(userType: string) {
    this.searchTerm = ''
    this.userType = userType;
    this.getCus_data();
  }

  createCustomer() {
    const modalRef = this.model.open(CreateCustomerComponent, {
      size: 'lg',
    });
    modalRef.componentInstance.fromParent = 'createCustomer';
    modalRef.result.then(
      (result) => {
        console.log('Modal Result', `Closed with: ${result}`);
      },
      (reason) => {
        if (reason == 'create') {
          this.toast.toastNotification('Customer Created Successfully', 'Customer Operation!');
          this.getCus_data();
        }
      }
    );
  }

  getDismissReason(reason: any): string {
    if (reason === ModalDismissReasons.ESC) {
      return 'by pressing ESC';
    } else if (reason === ModalDismissReasons.BACKDROP_CLICK) {
      return 'by clicking on a backdrop';
    } else if (reason === 'true') {
      this.getCus_data();
    }
  }

  delCustomer(id: number) {
    this.dialog.open(DialogboxComponent, {
      width: '300px',
    })
    .afterClosed()
    .subscribe((response) => {
      if (response == 'yes') {
        this.cusservice.delCustomer(id).subscribe(({ message, success }) => {
          if (success) {
            this.toast.toastNotification1(message, 'Customer Operation!');
            this.getCus_data();
          }
        });
      }
    });
  }

  updateActiveStatus(element: any) {
    let data = {
      id: element.id,
      admin_id: this.global.admin.admin_id,
    };
    this.cusservice.change_CustomerStatus(data).subscribe(({ msg, success }) => {
      if (success) {
        let status = 'Customer Operation!';
        this.toast.toastNotification(msg, status);
        this.getCus_data();
      }
    });
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


  activity() {
    this.trackAdmin.storeActivity('Customer', `Enter in Customer Page`).subscribe(({ success, id }) => {
      if (success) {
        localStorage.setItem('routerId', id)
      }
    })
  }

  ngOnDestroy() {
    this.trackAdmin.storeActivity('Customer', 'Exit Customer Page', localStorage.getItem('routerId')).subscribe(res => {
      if (res.success) {
        localStorage.removeItem('routerId');
      }
    })
  }

}
