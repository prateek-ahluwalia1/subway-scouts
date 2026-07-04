import { Component, OnInit, OnDestroy, AfterViewInit, ChangeDetectionStrategy, ChangeDetectorRef } from '@angular/core';
import { NgbModal, ModalDismissReasons } from '@ng-bootstrap/ng-bootstrap';
import { ChargeRateService } from 'app/services/charge-rate.service';
import { GlobalVariable } from 'app/shared/global';
import { Router } from '@angular/router';
import { TrackAdminActivityService } from 'app/services/track-admin-activity.service';
import { PermissionsService } from 'app/services/permissions.service';
import { ToastServiceService } from 'app/services/toast-service.service';
import { AddAwardRateComponent } from '../add-award-rate/add-award-rate.component';

@Component({
  selector: 'app-award-rate',
  templateUrl: './award-rate.component.html',
  styleUrls: ['./award-rate.component.scss']
})

export class AwardRateComponent implements OnInit {

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
    private changeDetect: ChangeDetectorRef
  ) { }

  ngOnInit(): void {
    this.initializeData();
    this.changeDetect.markForCheck()
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
    this.changeDetect.markForCheck()
  }

  payRates() {
    if (this.activeTab == 'Active') {
      this.chargeRateService.getAllAwardPayRates().subscribe(({ success, payrates, message }) => {
        if (success) {
          this.payRateList = payrates;
          this.filteredPayRateList = [...this.payRateList];
        }
        else {
          this.toastService.toastNotification1(message, 'Payrate Operation')
        }
        this.changeDetect.markForCheck()
      });
    }
    else {
      this.chargeRateService.getallarchivepayrates().subscribe(({ success, data }) => {
        if (success) {
          this.archivePayRateList = data;
        }
        this.changeDetect.markForCheck()
      });
    }
  }

  filterPayRates() {
    const searchTermLower = this.searchTerm.toLowerCase();
    this.filteredPayRateList = this.payRateList.filter(payrate =>
      payrate.title.toLowerCase().includes(searchTermLower));
  }

  openTemplateModal(rates?, status?) {
    const modalRef = this.modalService.open(AddAwardRateComponent, {
      scrollable: true,
      windowClass: 'create',
      size: 'lg'
    });

    let senddata = {
      rates: rates ? rates : '',
      type: status ? status : ''
    };

    modalRef.componentInstance.data = senddata;

    modalRef.result.then((result) => {
      if (result === 'update' || result === 'add') {
        setTimeout(() => {
          this.chargeRateService.getAllAwardPayRates().subscribe(res => {
            this.payRateList = res.payrates;
            this.filteredPayRateList = [...this.payRateList];
            this.changeDetect.markForCheck()
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
