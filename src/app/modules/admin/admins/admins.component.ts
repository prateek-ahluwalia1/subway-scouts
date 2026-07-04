import { Component, ElementRef, OnDestroy, OnInit, ViewChild } from '@angular/core';
import { MatTableDataSource } from '@angular/material/table';
import { NgbModal, ModalDismissReasons, NgbModalRef } from '@ng-bootstrap/ng-bootstrap';
import { Router, NavigationEnd } from '@angular/router';
import { MatDialog } from '@angular/material/dialog';
import { DialogboxComponent } from '../dialogbox/dialogbox.component';
import { ToastServiceService } from 'app/services/toast-service.service';
import { GlobalVariable } from 'app/shared/global';
import { TrackAdminActivityService } from 'app/services/track-admin-activity.service';
import { PermissionsService } from 'app/services/permissions.service';
import { CreateAdminComponent } from '../models/create-admin/create-admin.component';
import { ServiceService } from 'app/services/service.service';
import { AdminService } from 'app/services/admin.service';
import { filter } from 'rxjs';
import { MatPaginator } from '@angular/material/paginator';
import { MatSort } from '@angular/material/sort';
export interface PeriodicElement { }

const ELEMENT_DATA: PeriodicElement[] = [];

@Component({
  selector: 'app-admins',
  templateUrl: './admins.component.html',
  styleUrls: ['./admins.component.scss']
})

export class AdminsComponent implements OnInit, OnDestroy {

  adminList = [];
  displayedColumns: string[] = ['image', 'name', 'email', 'role', 'status', 'last_login', 'action'];
  dataSource = new MatTableDataSource<PeriodicElement>([]);
  userType = 'active';
  searchTerm: string = '';
  pageTitle: string;
  adminPermissions: any;
  closeAdminCreateModal: NgbModalRef;
  adminStatus: boolean;
  active: any;
  inactive: any;
  routeId: string;
  @ViewChild('adminId') myDiv: ElementRef;
  @ViewChild(MatPaginator) paginator: MatPaginator;
  @ViewChild(MatSort) sort: MatSort;
  isEdit: boolean = false;

  constructor(
    public toastService: ToastServiceService,
    private modalService: NgbModal,
    public global: GlobalVariable,
    public dialog: MatDialog,
    public router: Router,
    public server: ServiceService,
    private adminservice: AdminService,
    private trackAdmin: TrackAdminActivityService,
    private permissionService: PermissionsService
  ) {

    this.updatePageTitle();
    this.router.events
      .pipe(filter(event => event instanceof NavigationEnd))
      .subscribe((event: NavigationEnd) => {
        this.updatePageTitle(event.url);
      });

    let routerId = localStorage.getItem('routerId');
    if (!routerId) {
      this.activity();
    }
  }

  private updatePageTitle(url?: string): void {
    const isSalesPerson = (url || this.router.routerState.snapshot.url) === '/users/sales-person';
    this.pageTitle = isSalesPerson ? 'Sales Person' : 'Administrators';
  }

  ngOnInit() {
    const per = this.permissionService.getPermissionsByTitle('Onboarding');
    this.adminPermissions = this.pageTitle === 'Sales Person'
      ? per?.childPage?.find(item => item.title === 'Sales Person')
      : per?.childPage?.find(item => item.title === 'Admins');
    this.getAdminData();
  }

  getAdminData() {
    const data = { type: this.pageTitle === 'Sales Person' ? 'saleperson' : undefined };
    this.server.getAdmin(this.userType, data).subscribe(res => {
      if (res.success) {
        this.adminList = res.data;
        this.dataSource = new MatTableDataSource(this.adminList);
        this.dataSource.paginator = this.paginator;
        this.active = res.active;
        this.inactive = res.inactive;
      }
    }, (error) => {
      console.log(error);
    });
  }

  getUserType(userType: string) {
    this.searchTerm = ''
    this.userType = userType;
    this.getAdminData();
  }

  getDismissReason(reason: any): string {
    if (reason === ModalDismissReasons.ESC) {
      return 'by pressing ESC';
    } else if (reason === ModalDismissReasons.BACKDROP_CLICK) {
      return 'by clicking on a backdrop';
    } else if (reason === 'updated') {
      this.getAdminData();
    }
  }

  updateActiveStatus(element) {
    const isSalesPersonSection = this.pageTitle === 'Sales Person';
    this.adminStatus = element.status === 'active';
    const data = {
      id: element.id,
      admin_id: this.global.admin.admin_id,
      status: isSalesPersonSection
    };
    this.server.change_AdminStatus(data).subscribe(res => {
      if (res.success) {
        const status = this.pageTitle === 'Administrators' ? 'Admin Operation!' : 'Sale Person Operation!';
        this.toastService.toastNotification(res.msg, status);
        this.getAdminData();
        this.trackAdmin.storeActivity(this.pageTitle, `Changed status of ${this.pageTitle} name: ${element?.name} from ${element?.status} to ${element?.status == 'active' ? 'inactive' : 'active'}`, localStorage.getItem('routerId')).subscribe(({ success, id }) => {
        })
      }
    });
  }

  createAdmin(id?) {
    const isSalesPersonSection = this.pageTitle === 'Sales Person';
    if (id == 'new') {
      const modelRef = this.modalService.open(CreateAdminComponent, { windowClass: 'create-admin', size: 'lg' });
      modelRef.componentInstance.fromParent = 'new';
      modelRef.componentInstance.fromSalesPersonSection = isSalesPersonSection;
      modelRef.result.then((result) => {
        console.log('Modal Result', `Closed with: ${result}`);
      }, (reason) => {
        this.global.selectedCustomers = [];
        console.log('Modal Closed Reason -> ', `Dismissed ${this.getDismissReason(reason)}`);
      });
    } else {
      this.isEdit = true;
      this.adminservice.getAdminData(id).subscribe(res => {
        if (res.success === true) {
          const modalRef = this.modalService.open(CreateAdminComponent, {
            windowClass: 'create-admin',
            size: 'lg'
          });
          modalRef.componentInstance.fromParent = res;
          modalRef.componentInstance.fromParentedit = this.isEdit;
          modalRef.componentInstance.fromSalesPersonSection = isSalesPersonSection;
          modalRef.result.then((result) => {
            console.log('Modal Result', `Closed with: ${result}`);
          }, (reason) => {
            console.log('Modal Closed Reason -> ', `Dismissed ${this.getDismissReason(reason)}`);
          });
        }
      }, (error) => {
        console.log(error);
      });
    }
  }

  delAdmin(element) {
    this.dialog.open(DialogboxComponent, {
      width: '300px',
    }).afterClosed().subscribe(response => {
      if (response === 'yes') {
        const isSalesPersonSection = this.pageTitle === 'Sales Person';
        this.adminservice.delAdmin(element.id, isSalesPersonSection).subscribe(({ message, success }) => {
          if (success) {
            const status = this.pageTitle === 'Administrators' ? 'Admin Operation' : 'Sale Person Operation';
            this.toastService.toastNotification(message, status);
            this.getAdminData();
            this.trackAdmin.storeActivity(this.pageTitle, `Delete a ${this.pageTitle} with name: ${element?.name}`, localStorage.getItem('routerId')).subscribe(({ success, id }) => {
            })
          }
        }, (error) => {
          console.log(error);
        });
      }
    });
  }

  search() {
    if (!this.searchTerm || this.searchTerm.length === 0) {
      this.dataSource.data = this.adminList;
      return;
    }
    const results = this.adminList.filter(item => item.name.toLowerCase().indexOf(this.searchTerm.toLowerCase()) > -1);
    this.dataSource.data = results.length === 1 ? results : [];
  }

  profileAdmin(id) {
    this.router.navigate(['/profile'], { queryParams: { id: id } });
  }

  handleImageError(event: any) {
    event.target.src = 'assets/images/logo/scouts.png';
  }

  activity() {
    this.trackAdmin.storeActivity(this.pageTitle, `Enter in ${this.pageTitle} Page`).subscribe(({ success, id }) => {
      if (success) {
        localStorage.setItem('routerId', id)
      }
    })
  }

  ngOnDestroy() {
    this.trackAdmin.storeActivity(this.pageTitle, `Exit ${this.pageTitle} Page`, localStorage.getItem('routerId')).subscribe(res => {
      if (res.success) {
        localStorage.removeItem('routerId');
      }
    })
  }
}
