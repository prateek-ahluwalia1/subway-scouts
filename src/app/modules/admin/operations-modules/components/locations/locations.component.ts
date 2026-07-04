import { Component, OnInit, ViewChild, NgZone, Renderer2 } from '@angular/core';
import { MatTableDataSource } from '@angular/material/table';
import { SelectionModel } from '@angular/cdk/collections';
import { SiteService } from 'app/services/site.service';
import { ToastServiceService } from 'app/services/toast-service.service';
import { DateAdapter } from '@angular/material/core';
import { MatSort } from '@angular/material/sort';
import { ModalDismissReasons, NgbModal } from '@ng-bootstrap/ng-bootstrap';
import { CustomerService } from 'app/services/customer.service';
import { GlobalVariable } from 'app/shared/global';
import { Router } from '@angular/router';
import { FormBuilder, FormControl, FormGroup, Validators } from '@angular/forms';
import { PageEvent } from '@angular/material/paginator';
import { Title } from '@angular/platform-browser';
import { TrackAdminActivityService } from 'app/services/track-admin-activity.service';
import { PermissionsService } from 'app/services/permissions.service';
import { MatDialog } from '@angular/material/dialog';
import { SiteFormComponent } from './site-form/site-form.component';
import { SiteShiftsComponent } from 'app/modules/admin/site-shifts/site-shifts.component';
import { StaffService } from 'app/services/staff.service';
import { CreateSiteComponent } from 'app/modules/admin/models/create-site/create-site.component';

// import { MomentDateAdapter } from "@angular/material-moment-adapter";
export interface PeriodicElement {
  id: number;
  site_name: string;
  description: string;
  action: string;
}

const ELEMENT_DATA: PeriodicElement[] = [];

export class Customers {
  id: number;
  name: string;
}

@Component({
  selector: 'app-locations', 
  templateUrl: './locations.component.html',
  styleUrls: ['./locations.component.scss']
})

export class LocationsComponent implements OnInit {

  selectedType = 'all'
  searchTerm
  dates
  customer_id
  siteEdit: any = []
  displayedColumns: string[] = ['id', 'site_name', 'site_description', 'action'];
  customer: any = []
  dataSource = new MatTableDataSource<PeriodicElement>(ELEMENT_DATA);
  selection = new SelectionModel<PeriodicElement>(true, []);
  @ViewChild(MatSort) sort: MatSort;
  length: number;
  pageSize: number = 50;
  pageIndex: number = 0;
  pageSizeOptions: number[] = [5, 10, 25, 50];
  hidePageSize = false;
  showPageSizeOptions = true;
  showFirstLastButtons = true;
  disabled = false;
  pageEvent: PageEvent;
  AddCustomrSite: FormGroup;
  selectedSites: any
  originalData: any[] = [];
  customersIds: any[] = [];
  fromMultiCustomer: any = {};
  siteId;
  id;
  type;
  data;
  routeId
  adminPermissions: any;
  customers: Customers[] = [];
  AddDeleteReason: FormGroup;
  params
  modalRef;
  elementId;
  element
  msg;
  admindata;
  deleteReason
  locList = [];

  constructor(private titleService: Title,
    public zone: NgZone,
    private siteService: SiteService,
    private toast: ToastServiceService,
    public dateAdapter: DateAdapter<Date>, public renderer2: Renderer2,
    private cus: CustomerService,
    private modalService: NgbModal,
    public globals: GlobalVariable,
    public router: Router,
    private fb: FormBuilder,
    private trackAdmin: TrackAdminActivityService,
    private permissionService: PermissionsService,
    private staffService: StaffService,
    public dialog: MatDialog,) 
  {
    const per = this.permissionService.getPermissionsByTitle('WFM Tools');
    this.adminPermissions = per?.childPage?.find(item => item.title === 'Branches');
    console.log(this.adminPermissions);
    const activatedUrl = this.router.url;
    this.globals.ActivateUrl = activatedUrl
    this.dateAdapter.setLocale('en-AU')
    this.titleService.setTitle('Locations | The scouts');

    this.getAllSites();

    let routerId = localStorage.getItem('routerId')
    if (!routerId) {
      this.activity()
    }
  }

  ngOnInit() {
    this.cus.getCust().subscribe(({ success, data }) => {
      if (success) {
        this.customers = data
      }
    })

    this.AddDeleteReason = this.fb.group({
      reason: new FormControl('', Validators.required),
    })

    this.AddCustomrSite = this.fb.group({
      customer_id: new FormControl(''),
      site_type: new FormControl('direct'),
      site_name: new FormControl(''),
      site_description: new FormControl(''),
      site_start_date: new FormControl(''),
      site_end_date: new FormControl(''),
      job_instrcutions: new FormControl(''),
      staff_type: new FormControl('1'),
      unpublished_site: new FormControl('no'),
      site_trained: new FormControl(''),
      sos_phone: new FormControl(''),
      site_state: new FormControl(''),
      site_hours: new FormControl(''),
      po_wo: new FormControl(''),
      address: new FormControl(''),
      coordinates: new FormControl(''),
      signin_radius: new FormControl(''),
      radius_alert: new FormControl(''),
      site_level: new FormControl(''),
      payrol: new FormControl('default'),
      site_payrate_level: new FormControl(''),
      site_payrate: new FormControl(''),
      site_chargerate_level: new FormControl(''),
      site_charge_rate: new FormControl(''),
      site_break: new FormControl(''),
      site_break_payable: new FormControl(''),
      site_break_chargeable: new FormControl(''),
      break_deduction_payable: new FormControl(''),
      break_deduction_chargeable: new FormControl(''),
      welfare_call: new FormControl('no'),
      welfare_call_type: new FormControl(''),
      welfare_timing: new FormControl(''),
      green_call: new FormControl('no'),
      first_green_call: new FormControl(''),
      second_green_call: new FormControl(''),
      first_green_call_time: new FormControl(''),
      second_green_call_time: new FormControl(''),
      job_instruction_file: new FormControl(''),
      site_update_reason: new FormControl(''),
      type: new FormControl('metro'),
      site_tasks: this.fb.array([]),
      is_patrolling_site: new FormControl(false),
      monitoring_person: new FormControl(''),
      monitoring_contact: new FormControl(''),
      after_hours: new FormControl(''),
      is_alarm_patrol_site: new FormControl(''),
      internal_patrolling: new FormControl(false),
      external_patrolling: new FormControl(false),
      intermediate_patrolling: new FormControl(false),
      scanners: this.fb.array([]),
      keys: this.fb.array([]),
      alarm_panels: this.fb.array([]),
      alarm_dispatch_instruction: new FormControl(''),
      intern_no_calls: new FormControl(''),
      extern_no_calls: new FormControl(''),
      intermed_no_calls: new FormControl(''),
      intern_time_type: new FormControl(''),
      extern_time_type: new FormControl(''),
      intermed_time_type: new FormControl(''),
      dateSelectionOfPay: new FormControl('same_day'),
      dateSelectionOfCharge: new FormControl('same_day'),
      intern_particular_times: this.fb.array([]),
      extern_particular_times: this.fb.array([]),
      intermed_particular_times: this.fb.array([]),
    })
  }

  clickNewSite() {
    this.siteService.addSite(this.AddCustomrSite.value).subscribe(({ success, id, message }) => {
      const status = 'Location Operation!';
      if (success) {
        this.siteId = id;
        const modalRef = this.modalService.open(SiteFormComponent, { size: 'xl', backdrop: 'static' });
        modalRef.componentInstance.patrolsiteId = this.siteId;
        modalRef.componentInstance.routeId = this.routeId;
        modalRef.result.then(
          (result) => {
            console.log(result);
            console.log("Modal Result", `Closed with: ${result}`);
          },
          (reason) => {
            console.log(reason);
            let rea = this.getDismissReason(reason)
            if (rea == 'update') {
              this.getAllSites()
            }
          }
        );
      }
      else {
        this.toast.toastNotification(message, status);
      }
    },
    (error) => {
      this.toast.toastNotification('Something went wrong. Please contact the support team.', 'Request Incomplete!');
    });
  }

  // search() {
  //   // this.dataSource.filter = this.searchTerm.toLowerCase();
  //   // console.log("this.dataSource.filter ", this.dataSource.filter);
  //   if (this.searchTerm.trim() === '') {
  //     this.dataSource.data = this.originalData;
  //   } else {
  //     const lowerCaseTerm = this.searchTerm.trim().toLowerCase();
  //     this.dataSource.data = this.originalData.filter(location =>
  //       location.site_name.toLowerCase().includes(lowerCaseTerm)
  //     );
  //   }
  // }

  search() {
    if (this.searchTerm) {
      const eventValue = this.pageEvent;
      let data = {
        searchTerm: this.searchTerm,
      }
      this.staffService.searchLocation(data).subscribe(({ success, data, length }) => {
        if (success) {
          this.locList = data;
          this.dataSource.data = this.locList;
        }
      })
    }
  }

  getAllSites() {
    const eventValue = this.pageEvent;

    if (this.customersIds.length == 0) {
      this.params = {
        status: this.selectedType,
        length: eventValue ? eventValue.length : 0,
        pageIndex: eventValue ? eventValue.pageIndex : 0,
        pageSize: eventValue ? eventValue.pageSize : this.pageSize,
        previousPageIndex: eventValue ? eventValue.previousPageIndex : 0,
      }
    }
    else if (this.customersIds.length > 0) {
      this.params = {
        length: eventValue ? eventValue.length : 0,
        pageIndex: eventValue ? eventValue.pageIndex : 0,
        pageSize: eventValue ? eventValue.pageSize : this.pageSize,
        previousPageIndex: eventValue ? eventValue.previousPageIndex : 0,
        customer_ids: this.customersIds,
        status: this.selectedType
      }
    }

    this.siteService.getAllSites(this.params).subscribe((response: any) => {
      if (response.success) {
        this.originalData = response.data;
        this.dataSource = new MatTableDataSource(response.data);
        this.length = response.length
      }
    });
  }

  generateSiteLocationReport() {
    const exportParams: any = {
      status: this.selectedType,
    };

    if (this.customersIds.length > 0) {
      exportParams.customer_ids = this.customersIds;
    }

    this.siteService.getAllSites(exportParams).subscribe(
      ({ success, data }) => {
        if (success) {
          this.dataSource.data = data;
          this.toast.toastNotification('Data ready for export.', 'Export Success!');
        } else {
          this.toast.toastNotification('Failed to fetch data for export.', 'Export Failed!');
        }
      },
      (error) => {
        console.error('Export error:', error);
        this.toast.toastNotification('Something went wrong during export.', 'Export Failed!');
      }
    );
  }

  // receiveDataFromChild(data: string) {
  //   this.fromMultiCustomer = data;
  //   if (Array.isArray(this.fromMultiCustomer.value)) {
  //     this.customersIds = this.fromMultiCustomer.value.map(item => item.id);
  //     if (this.customersIds) {
  //       this.getAllSites()
  //     }
  //   }
  // }
  receiveDataFromChild(data: any) {
  this.fromMultiCustomer = data;
  if (Array.isArray(this.fromMultiCustomer.value)) {
    this.customersIds = this.fromMultiCustomer.value.map(item => item.id);
    if (this.customersIds) {
      this.getAllSites(); // Update the table
    }
  } else {
    this.customersIds = [];
    this.getAllSites();
  }
}

  editSite(id) {
    const modalRef = this.modalService.open(SiteFormComponent, { size: 'xl' });
    modalRef.componentInstance.fromParent = id;
    modalRef.componentInstance.routeId = this.routeId;
    modalRef.result.then(
      (result) => {
        console.log(result);
        console.log("Modal Result", `Closed with: ${result}`);
      },
      (reason) => {
        console.log(reason);
        let rea = this.getDismissReason(reason)
        if (rea == 'update') {
          this.getAllSites()
        }
      }
    );
  }

  openVerticallyCentered(element, content) {
    this.elementId = element.id;
    this.element = element;
    this.modalRef = this.modalService.open(content, { centered: true });
  }

  deletedata(id) {
    this.elementId = id;
    let data = {
      id: this.elementId,
      reason: this.AddDeleteReason.value.reason,
      admin_id: this.globals.admin.admin_id
    }
    this.siteService.delSite(data).subscribe(({ message, success }) => {
      if (success) {
        this.toast.toastNotification(message, 'Site Operation!')
        this.getAllSites();
        this.AddDeleteReason.reset();
        this.trackAdmin.storeActivity('Site', `Delete Site: ${this.element.site_name} with reason: ${this.AddDeleteReason.value.reason}`, this.routeId).subscribe(() => {
        })
      }
      else {
        this.dialog.open(SiteShiftsComponent, {
          width: '500px',
          data: {
            message: message
          }
        }).afterClosed().subscribe(response => {
          if (response === 'yes') {
            let data = {
              id: this.elementId,
              reason: this.AddDeleteReason.value.reason,
              is_confirm: 'yes',
              admin_id: this.globals.admin.admin_id
            }
            this.siteService.delSite(data).subscribe(({ message, success }) => {
              if (success) {
                this.toast.toastNotification(message, 'Site Operation!')
                this.getAllSites();
                this.AddDeleteReason.reset();
              }
            });
          }
        });
      }
    },
    (error) => {
      console.log(error);
    });
    this.modalRef.close();
  }

  openComment(id, comment) {
    this.elementId = id;
    this.siteService.getSpecificSite(id).subscribe(({ success, data, site_update_reason }) => {
      if (success) {
        this.msg = site_update_reason
        this.admindata = data
      }
    })
    this.modalRef = this.modalService.open(comment, { centered: true });
  }

  openLg(deletesite) {
    this.siteService.getdelSiteReason().subscribe(({ success, data }) => {
      if (success) {
        this.deleteReason = data;
      }
    })
    this.modalService.open(deletesite, { size: 'lg' });
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

  siteStatus(value) {
    this.selectedType = value
    this.getAllSites()
  }

  handlePageEvent(e: PageEvent) {
    this.pageEvent = e;
    this.length = e.length;
    this.pageSize = e.pageSize;
    this.pageIndex = e.pageIndex;
    this.getAllSites()
  }

  setPageSizeOptions(setPageSizeOptionsInput: string) {
    if (setPageSizeOptionsInput) {
      this.pageSizeOptions = setPageSizeOptionsInput.split(',').map(str => +str);
    }
  }

  ngOnDestroy() {
    // localStorage.removeItem('routerId');

    // this.trackAdmin.storeActivity('Exit Sites Page', 'Exit Sites Page', this.routeId).subscribe(res => {
    // })
  }

  activity() {
    this.trackAdmin.storeActivity('Sites Page', 'Enter in Sites Page').subscribe(({ success, id }) => {
      if (success) {
        localStorage.setItem('routerId', id)
        this.routeId = id
      }
    })
  }
}

