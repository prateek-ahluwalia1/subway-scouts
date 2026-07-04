import { Component, OnInit, OnDestroy, AfterViewInit, ChangeDetectionStrategy, ChangeDetectorRef } from '@angular/core';
import { NgbModal, ModalDismissReasons } from '@ng-bootstrap/ng-bootstrap';
import { ChargeRateService } from 'app/services/charge-rate.service';
import { GlobalVariable } from 'app/shared/global';
import { Router } from '@angular/router';
import { TrackAdminActivityService } from 'app/services/track-admin-activity.service';
import { PermissionsService } from 'app/services/permissions.service';
import { ToastServiceService } from 'app/services/toast-service.service';
import { AddPayrateComponent } from '../add-payrate/add-payrate.component';

@Component({
  selector: 'app-pay-rates',
  templateUrl: './pay-rates.component.html',
  styleUrls: ['./pay-rates.component.scss'],
  changeDetection: ChangeDetectionStrategy.OnPush

})
export class PayRatesComponent implements OnInit {

  payRate: any;
  payRateList: any[];
  archivePayRateList: any[];
  searchTerm: string;
  activeTab = 'Active';
  enableMoreOptions = false;
  tabs = [
    {
      id: 1,
      icon: 'check_circle_outline',
      name: 'Active'
    },
    {
      id: 2,
      icon: 'archive',
      name: 'Archive'
    },
  ];

  adminPermission: any;
  routeId: any;
  filteredPayRateList: any[] = [];

  constructor(
    private modalService: NgbModal,
    public chargeRateService: ChargeRateService,
    public toastService: ToastServiceService,
    public globals: GlobalVariable,
    private router: Router,
    private trackAdmin: TrackAdminActivityService,
    private permissionService: PermissionsService,
    private chnageDetect: ChangeDetectorRef
  ) { }

  ngOnInit(): void {
    this.initializeData();
    this.chnageDetect.markForCheck()
  }

  initializeData() {
    const per = this.permissionService.getPermissionsByTitle('Accounts');
    this.adminPermission = per?.childPage?.find(item => item.title === 'Pay Rates');

    const activatedUrl = this.router.url;
    this.globals.ActivateUrl = activatedUrl;

    this.payRates();

    let routerId = localStorage.getItem('routerId');
    if (!routerId) {
      this.activity();
    }
  }

  activity() {
    this.trackAdmin.storeActivity('Pay Rates List Page', 'Enter in Pay Rates List Page').subscribe(({ success, id }) => {
      if (success) {
        localStorage.setItem('routerId', id);
        this.routeId = id;
      }
    });
  }

  archive(rate) {
    let data = {
      payrate_id: rate.id
    };
    this.chargeRateService.makeArchive(data).subscribe((res) => {
      const status = 'PayRate Operation';
      if (res.success) {
        this.toastService.toastNotification(res.message, status);
        this.payRates();
      } else {
        this.toastService.toastNotification1(res.message, status);
      }
    });
    this.chnageDetect.markForCheck()
  }

  payRates() {
    if (this.activeTab == 'Active') {
      this.chargeRateService.getAllPayRates().subscribe(({ success, data, message }) => {
        if (success) {
          this.payRateList = data;
          this.filteredPayRateList = [...this.payRateList];
        }
        else {
          this.toastService.toastNotification1(message, 'Payrate Operation')
        }
        this.chnageDetect.markForCheck()
      });
    }
    else {
      this.chargeRateService.getallarchivepayrates().subscribe(({ success, data }) => {
        if (success) {
          this.archivePayRateList = data;
        }
        this.chnageDetect.markForCheck()
      });
    }
  }

  filterPayRates() {
    const searchTermLower = this.searchTerm.toLowerCase();
    this.filteredPayRateList = this.payRateList.filter(payrate =>
      payrate.title.toLowerCase().includes(searchTermLower));
  }

  openTemplateModal(rates?, status?) {
    const modalRef = this.modalService.open(AddPayrateComponent, {
      scrollable: true,
      windowClass: 'create',
      size: 'lg'
    });

    let data = {
      rates: rates ? rates : '',
      type: status ? status : ''
    };
    modalRef.componentInstance.data = data;

    modalRef.result.then((result) => {
      if (result === 'update' || result === 'add') {
        setTimeout(() => {
          this.chargeRateService.getAllPayRates().subscribe(res => {
            this.payRateList = res.data;
            this.filteredPayRateList = [...this.payRateList];
            this.chnageDetect.markForCheck()
          });
        }, 1000);
      }
    }, (reason) => {
      console.log(`Modal Closed Reason -> Dismissed ${this.getDismissReason(reason)}`);
    });
  }

  getDismissReason(reason: any): string {
    if (reason === ModalDismissReasons.ESC) {
      return 'by pressing ESC';
    } else if (reason === ModalDismissReasons.BACKDROP_CLICK) {
      return 'by clicking on a backdrop';
    } else if (reason === 'updated') {
      return reason;
    }
    return '';
  }

  getTab(tab) {
    this.activeTab = tab.name;
    this.payRates();
  }

  editRates(rate, status) {
    this.openTemplateModal(rate, status);
  }

  trackById(index: number, item: any) {
    return item.id;
  }

}
