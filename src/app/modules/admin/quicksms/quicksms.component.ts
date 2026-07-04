import { ToastServiceService } from './../../../services/toast-service.service';
import { SiteService } from "./../../../services/site.service";
import { SmsServicesService } from "./../../../services/sms-services.service";
import { Component, ElementRef, OnInit, ViewChild } from "@angular/core";
import { FormBuilder, FormGroup } from "@angular/forms";
import { NgbPopover } from "@ng-bootstrap/ng-bootstrap";
import { CustomerService } from "app/services/customer.service";
import { AppearanceAnimation, ConfirmBoxInitializer, DialogLayoutDisplay, DisappearanceAnimation } from '@costlydeveloper/ngx-awesome-popup';
import { MatChipInputEvent } from '@angular/material/chips';
import { COMMA, ENTER } from '@angular/cdk/keycodes';
import { StaffService } from 'app/services/staff.service';
import { GlobalVariable } from 'app/shared/global';
import { Router } from '@angular/router';
import { TrackAdminActivityService } from 'app/services/track-admin-activity.service';
import { PermissionsService } from 'app/services/permissions.service';


export interface Cc {
  email: string;
}
export interface Bcc {
  email: string;
}
export interface newStaff {
  number: string;
}
@Component({
  selector: "app-quicksms",
  templateUrl: "./quicksms.component.html",
  styleUrls: ["./quicksms.component.scss"],
})

export class QuicksmsComponent implements OnInit {
  @ViewChild("myPopover") myPopover: NgbPopover;
  message = "";
  smsform: any;
  customers: any;
  sitesList: any;
  addsmsForm: FormGroup;
  templetes: any[] = []
  isNewGuard: boolean = false

  newStaff: newStaff[] = [];

  ////
  //test cc
  bcc: Bcc[] = [];
  //
  cc: Cc[] = [];


  sitesArray: any = [];
  // isPopoverVisible: boolean;
  isPopoverVisible = false;
  guard_status;
  site_status;

  sitesIds;
  guards;


  //test cc
  visible = true;
  selectable = true;
  removable = true;
  addOnBlur = true;
  readonly separatorKeysCodes: number[] = [ENTER, COMMA];

  id;
  type;
  data;
  routeId
  smsPermissions
  constructor(
    public fb: FormBuilder,
    public smsService: SmsServicesService,
    private cus: CustomerService,
    private site: SiteService,
    private toastService: ToastServiceService,
    private userService: StaffService,
    private globals: GlobalVariable,
    private router: Router,
    private trackAdmin: TrackAdminActivityService,
    private permissionService: PermissionsService

  ) {

    const per = this.permissionService.getPermissionsByTitle('Communications');
    this.smsPermissions = per?.childPage?.find(item => item.title === 'Quick SMS');

    const activatedUrl = this.router.url;
    this.globals.ActivateUrl = activatedUrl

    // get all sms templetes 
    this.getTempletes();

    // get all guards
    this.guardsList()

    let routerId = localStorage.getItem('routerId')
    if (!routerId) {
      this.activity()
    }


  }
  ngOnInit(): void {
    this.cus.getCust().subscribe(
      (res) => {
        if (res.success == true) {
          this.customers = res.data;
        }
      },
      (error) => { }
    );

    //get all sites

    this.site.getAllSites().subscribe(
      (res) => {
        if (res.success == true) {
          this.sitesArray = res.data;
        }
      },
      (error) => { }
    );

    this.addsmsForm = new FormGroup({});
  }

  ngOnDestroy() {
    localStorage.removeItem('routerId');

    this.trackAdmin.storeActivity('Exit Quick SMS Page', 'Exit Quick SMS Page', this.routeId).subscribe(res => {
    })
  }

  activity() {
    this.trackAdmin.storeActivity('Quick SMS Page', 'Enter in Quick SMS Page').subscribe(({ success, id }) => {
      if (success) {
        localStorage.setItem('routerId', id)
        this.routeId = id
      }
    })
  }

  guardsIds;
  fromMultiGuard;

  ////send this is id in filter
  customersIds
  fromMultiCustomer
  receiveDataFromChild(data: string) {
    this.fromMultiCustomer = data;
    this.customersIds = this.fromMultiCustomer.value.map(item => item.id);
    if (this.fromMultiCustomer) {
      this.userService.getCusSite(this.customersIds).subscribe(res => {
        if (res.success) {
          this.sitesList = res.data
        }
      })
    }
  }


  // selected guards 
  receiveDataFromChildGuards(data: any) {
    this.fromMultiGuard = data;
    this.guardsIds = this.fromMultiGuard.value.map((item) => item.id);
  }

  // selected sites 
  receiveDataFromChildSite(data: any) {
    this.sitesIds = data?.value.map((item) => item.id);
  }

  // apply filter function

  applyFilters(data) {
    data.customer_id = this.customersIds
    data.site_id = this.sitesIds
    this.myPopover.close();
    this.smsService.getAllFilteredGuards(data).subscribe((res) => {
      if (res.success == true) {
        this.guards = res.data;
      }
    });
  }

  closePopover() {
    this.myPopover.close();
  }

  // getTempletes sms
  getTempletes() {
    this.smsService.getAllTempletes().subscribe(({ templates, success }) => {
      if (success) {
        this.templetes = templates;
      }
    })
  }

  templete(msg) {
    this.message = msg;
  }


  accept(data) {
    data.to = this.guardsIds;
    data.phone = this.newStaff
    this.openConfirmBox(data);
  }


  // standard typescript method.
  openConfirmBox(data) {
    const newConfirmBox = new ConfirmBoxInitializer();
    newConfirmBox.setTitle('Confirm Action');
    newConfirmBox.setMessage('Are you sure you want to confirm this action?');
    // Choose layout color type
    newConfirmBox.setConfig({
      layoutType: DialogLayoutDisplay.SUCCESS, // SUCCESS | INFO | NONE | DANGER | WARNING
      animationIn: AppearanceAnimation.SWING, // BOUNCE_IN | SWING | ZOOM_IN | ZOOM_IN_ROTATE | ELASTIC | JELLO | FADE_IN | SLIDE_IN_UP | SLIDE_IN_DOWN | SLIDE_IN_LEFT | SLIDE_IN_RIGHT | NONE
      animationOut: DisappearanceAnimation.BOUNCE_OUT, // BOUNCE_OUT | ZOOM_OUT | ZOOM_OUT_WIND | ZOOM_OUT_ROTATE | FLIP_OUT | SLIDE_OUT_UP | SLIDE_OUT_DOWN | SLIDE_OUT_LEFT | SLIDE_OUT_RIGHT | NONE
      allowHtmlMessage: true,
      buttonPosition: 'right', // optional 
    });
    newConfirmBox.setButtonLabels('Confirm', 'Decline');
    // Simply open the popup and observe button click
    data.admin_id = this.globals.admin.admin_id
    newConfirmBox.openConfirmBox$().subscribe(resp => {
      if (resp.success) {
        this.smsService.smsSend(data).subscribe(({ message, success }) => {
          if (success) {
            this.message = '';
            this.newStaff.length = 0
            this.guardsList()
            let status = 'SMS Operation!';
            this.toastService.toastNotification(message, status)
          }
          else {
            this.toastService.toastNotification1(message, 'Invalid Number!')
          }

        }, (error => {
          this.toastService.toastNotification1('Something went wrong. Please contact with support team.', 'Invalid Number!')

        }));

      }
    });
  }

  newGuard(value: HTMLInputElement) {
    this.isNewGuard = value.checked
  }

  add(event: MatChipInputEvent): void {
    const input = event.input;
    var valueIs = (event.value || '').trim();
    if (valueIs.charAt(0) === '0') {
      valueIs = valueIs.substring(1);
    } else {
      valueIs = valueIs;
    }
    // Add our fruit
    if (valueIs) {
      let no = '+61'.concat(valueIs);
      //  parseInt
      this.newStaff.push({ number: no });
    }
    // Reset the input value
    if (input) {
      input.value = '';
    }
  }


  remove(fruit: newStaff): void {
    const index = this.newStaff.indexOf(fruit);
    if (index >= 0) {
      this.newStaff.splice(index, 1);
    }
  }

  guardsList() {
    this.smsService.getAllFilteredGuards().subscribe(({ success, data }) => {
      if (success) {
        this.guards = data;
      }
    });
  }
  //
}
