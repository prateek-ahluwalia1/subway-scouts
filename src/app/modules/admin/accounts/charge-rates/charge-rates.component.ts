import { Component, OnInit, OnDestroy, ChangeDetectorRef, ChangeDetectionStrategy } from '@angular/core';
import { NgbModal, ModalDismissReasons } from '@ng-bootstrap/ng-bootstrap';
import { ChargeRateService } from 'app/services/charge-rate.service';
import { GlobalVariable } from 'app/shared/global';
import { Router } from '@angular/router';
import { TrackAdminActivityService } from 'app/services/track-admin-activity.service';
import { PermissionsService } from 'app/services/permissions.service';
import { ToastServiceService } from 'app/services/toast-service.service';
import { AddChargeRateComponent } from '../add-charge-rate/add-charge-rate.component';

@Component({
  selector: 'app-charge-rates',
  templateUrl: './charge-rates.component.html',
  styleUrls: ['./charge-rates.component.scss'],
  changeDetection: ChangeDetectionStrategy.OnPush
})

export class ChargeRatesComponent implements OnInit {

  activeChargeList: any[] = [];
  archiveChargeList: any[];
  chargeRate: any;
  activeTab = 'Active';
  enableMoreOptions = false;
  searchTerm: string = '';

  tabs = [
    { id: 1, icon: 'check_circle_outline', name: 'Active' },
    { id: 2, icon: 'archive', name: 'Archive' },
  ];

  adminPermission: any;
  routeId: any;
  filteredChargeList: any[] = [];

  constructor(
    private modalService: NgbModal,
    public chargeRateService: ChargeRateService,
    public toastService: ToastServiceService,
    private globals: GlobalVariable,
    private router: Router,
    private trackAdmin: TrackAdminActivityService,
    private permissionService: PermissionsService,
    private cdr: ChangeDetectorRef
  ) {
    const per = this.permissionService.getPermissionsByTitle('Accounts');
    this.adminPermission = per?.childPage?.find((item) => item.title === 'Charge Rates');

    const activatedUrl = this.router.url;
    this.globals.ActivateUrl = activatedUrl;

    this.chargeRates();

    let routerId = localStorage.getItem('routerId');
    if (!routerId) {
      this.activity();
    }
    this.cdr.markForCheck()
  }

  ngOnInit(): void {
    this.cdr.detectChanges();
  }

  activity() {
    this.trackAdmin.storeActivity('Charge Rate Page', 'Enter in Charge Rate Page').subscribe(({ success, id }) => {
      if (success) {
        localStorage.setItem('routerId', id);
        this.routeId = id;
      }
    });
  }

  archive(rate) {
    const data = { chargerate_id: rate.id };
    this.chargeRateService.makeArchiveCharge(data).subscribe((res) => {
      const status = 'Charge Rate Operation';
      this.toastService.toastNotification(res.success ? res.message : res.message, status);
      this.chargeRates();
      this.cdr.markForCheck()
    });
  }

  chargeRates() {
    this.chargeRateService.getAllchargeRates().subscribe(
      ({ status, charged_rates }) => {
        if (status) {
          // name === 'Active' ? (this.activeChargeList = data) : (this.archiveChargeList = data);
          // if (name === 'Active') {
          //   this.activeChargeList = data;
          //   this.filteredChargeList = data;
          // } else {
          //   this.archiveChargeList = data.charged_rates;
          // }
          this.activeChargeList = charged_rates;
          this.filteredChargeList = charged_rates;
        }
        this.cdr.markForCheck()
      },
      (error) => {
        console.log('error ', error);
      }
    );
  }

  filterChargeList() {
    if (this.searchTerm.trim() === '') {
      this.filteredChargeList = this.activeChargeList;
    } 
    else {
      this.filteredChargeList = this.activeChargeList.filter((charge) =>
        charge.title.toLowerCase().includes(this.searchTerm.toLowerCase())
      );
    }
  }

  openTemplateModal(rates?, status?) {
    const modalRef = this.modalService.open(AddChargeRateComponent, {
      scrollable: true,
      windowClass: 'create',
      size: 'lg',
    });

    let data = { rates: rates || '', type: status || '' };
    modalRef.componentInstance.data = data;

    modalRef.result.then(
      (result) => {
        this.chargeRates();
        this.cdr.markForCheck()
      },
      (reason) => {
        console.log(reason);
        console.log('Modal Closed Reason -> ', `Dismissed ${this.getDismissReason(reason)}`);
      }
    );
  }

  getDismissReason(reason: any): string {
    if (reason === ModalDismissReasons.ESC) {
      return 'by pressing ESC';
    } else if (reason === ModalDismissReasons.BACKDROP_CLICK) {
      return 'by clicking on a backdrop';
    } else if (reason === 'updated') {
      console.log(reason);
    }
  }

  getTab(tab) {
    this.activeTab = tab.name;
    this.chargeRates();
    this.cdr.markForCheck()
  }

  editRates(rate, status) {
    this.openTemplateModal(rate, status);
  }

}
