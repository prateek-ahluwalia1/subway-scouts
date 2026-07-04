import { Component, ElementRef, OnInit, ViewChild } from '@angular/core';
import { NgbPopover } from '@ng-bootstrap/ng-bootstrap';
import { CustomerService } from 'app/services/customer.service';
import { EmailService } from 'app/services/email.service';
import { SmsServicesService } from 'app/services/sms-services.service';
import { StaffService } from 'app/services/staff.service';
import { ToastServiceService } from 'app/services/toast-service.service';
import { COMMA, ENTER } from '@angular/cdk/keycodes';
import { MatChipInputEvent } from '@angular/material/chips';
import { GlobalVariable } from 'app/shared/global';
import { Router } from '@angular/router';
import { ContractorService } from 'app/services/contractor.service';
import { TrackAdminActivityService } from 'app/services/track-admin-activity.service';
import { PermissionsService } from 'app/services/permissions.service';
export class Customers {
  id: number;
  name: string;
}
export class Sites {
  id: number;
  site_name: string;
}

export interface Cc {
  email: string;
}
export interface Bcc {
  email: string;
}
export interface newStaff {
  email: string;
}
@Component({
  selector: 'app-quick-email',
  templateUrl: './quick-email.component.html',
  styleUrls: ['./quick-email.component.scss']
})
export class QuickEmailComponent implements OnInit {


  ///// new guards
  newStaff: newStaff[] = [];

  ////
  //test cc
  bcc: Bcc[] = [];
  //
  cc: Cc[] = [];
  selected = '';
  emailTemp
  signature: any = []
  emails: any = []
  //test cc
  bccList
  visible = true;
  selectable = true;
  removable = true;
  addOnBlur = true;

  readonly separatorKeysCodes: number[] = [ENTER, COMMA];

  add(event: MatChipInputEvent, type): void {
    const input = event.input;
    const value = event.value;
    if ((value || '').trim()) {
      if (type == 'cc') {
        this.cc.push({ email: value.trim() });
      }
      else if (type == 'bcc') {
        this.bcc.push({ email: value.trim() });
      }
      else if (type == 'newStaff') {
        this.newStaff.push({ email: value.trim() });
      }
    }
    if (input) {
      input.value = '';
    }
  }

  remove(fruit: Bcc, type): void {
    if (type == 'cc') {
      const index = this.cc.indexOf(fruit);
      if (index >= 0) {
        this.cc.splice(index, 1);
      }
    }
    if (type == 'bcc') {
      const index = this.bcc.indexOf(fruit);
      if (index >= 0) {
        this.bcc.splice(index, 1);
      }
    }
    if (type == 'newStaff') {
      const index = this.newStaff.indexOf(fruit);
      if (index >= 0) {
        this.newStaff.splice(index, 1);
      }
    }

  }
  //

  message = '';
  emailform: any;
  @ViewChild("myPopover") myPopover: NgbPopover;
  sitesList: any
  guardsList: any
  sitesArray: any = [];
  isPopoverVisible = false;
  guard_status;
  site_status;
  guardsIds: any = [];
  fromMultiGuard;

  otherCustomers: any = []
  otherContractor: any = []
  checked = true;
  customerch = false;
  contractorch = false;
  isNewGuard = false;

  user = {
    to: '',
    cc: [],
    bcc: [],
    new_guard: [],
    subject: '',
    message: '',
    email_view: '',
    signature_view: '',
  };
  receiveDataFromChildGuards(data: any) {
    this.fromMultiGuard = data;
    this.guardsIds = this.fromMultiGuard.value.map((item) => item.id);
    if (this.guardsIds) {
      this.user.to = this.guardsIds
    }
  }

  id;
  type;
  data;
  routeId
  quickPermissions

  constructor(private emailServices: EmailService,
    public smsService: SmsServicesService,
    private cus: CustomerService,
    public userService: StaffService,
    private toast: ToastServiceService,
    private global: GlobalVariable, private router: Router, private server: ContractorService,
    private trackAdmin: TrackAdminActivityService, private permissionService: PermissionsService) {
    const per = this.permissionService.getPermissionsByTitle('Communications');
    this.quickPermissions = per?.childPage?.find(item => item.title === 'Quick Email');

    const activatedUrl = this.router.url;
    this.global.ActivateUrl = activatedUrl

    let routerId = localStorage.getItem('routerId')
    if (!routerId) {
      this.activity()
    }

  }

  ngOnInit(): void {



    this.global.selectedCustomers = []

    // call custoemr function and guards
    this.getGuards()
    this.getCustomer()

    this.getContractor_data()

  }

  ngOnDestroy() {
    localStorage.removeItem('routerId');

    this.trackAdmin.storeActivity('Exit Quick Email Page', 'Exit Quick Email Page', this.routeId).subscribe(res => {
    })
  }

  activity() {
    this.trackAdmin.storeActivity('Quick Email Page', 'Enter in Quick Email Page').subscribe(({ success, id }) => {
      if (success) {
        localStorage.setItem('routerId', id)
        this.routeId = id
      }
    })
  }

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


  customersIds1
  fromMultiCustomer1
  contractorIds
  fromMultiContractor
  receiveDataFromChilds(data: string) {
    this.fromMultiCustomer1 = data;
    this.customersIds1 = this.fromMultiCustomer1.value.map(item => item.id);
  }


  receiveDataFromChilds1(data: string) {
    this.fromMultiContractor = data;
    this.contractorIds = this.fromMultiContractor.value.map(item => item.id);
  }

  sitesIds
  receiveDataFromChildSite(data: any) {
    this.sitesIds = data?.value.map((item) => item.id);
  }

  ////Apply filter on guard selection
  applyFilters(data) {
    data.customer_id = this.customersIds
    data.site_id = this.sitesIds
    this.myPopover.close();
    this.smsService.getAllFilteredGuards(data).subscribe(({ success, data }) => {
      if (success) {
        this.guardsList = data;
      }
    });
  }


  closePopover() {
    this.myPopover.close();
  }

  // get all guards
  getGuards() {
    let data = {
      pageIndex: 0,
      pageSize: 10000,
    }
    this.userService.getStaff(data).subscribe(res => {
      if (res.success) {
        res.data.forEach(element => {
          element.name = element.first_name + ' ' + element.last_name
        });
        this.guardsList = res.data
      }

    })
  }
  ///get customer
  customers: Customers[] = [];
  getCustomer() {
    this.cus.getCust().subscribe(res => {
      if (res.success == true) {
        this.customers = res.data
      }
    }, (error => {

    }))
  }



  ///////send Email
  submitted: boolean
  onSubmit(form) {

    const { cc, bcc, newStaff } = this;
    const { message } = form.value;

    form.value.to = this.guardsIds;
    form.value.cc = cc;
    form.value.newStaff = newStaff;
    form.value.bcc = bcc;
    form.value.email_type = this.emailTemp;
    form.value.admin_id = this.global.admin.admin_id
    form.value.customers_ids = this.customersIds1
    form.value.contractor_ids = this.contractorIds
    let status = 'Empty Staff!'
    let msg = "Please select at least one staff"
    this.submitted = true;
    if (this.guardsIds.length == 0 && !this.isNewGuard) {
      this.toast.toastNotification1(msg, status)
      return false
    }

    if (!this.emailTemp) {
      let status = 'Quick Email!';
      let msg = 'Please select or write at least one email template';
      this.toast.toastNotification1(msg, status);
      return false; // Prevent form submission
    }

    if (!form.value.message) {
      this.toast.toastNotification1('Message filed can not be empty', 'Form Incomplete!');
      return false; // Prevent form submission
    }
    console.log(form.value);

    // Send email
    this.emailServices.sendEmail(form.value).subscribe(({ success, message }) => {
      if (success) {
        const status = 'Email Operation!';
        this.toast.toastNotification(message, status);
        form.reset();
        this.bcc = []
        this.cc = []
        this.newStaff = []
        this.submitted = false
        this.user.signature_view = ''
        this.user.email_view = ''

      }
    });
  }




  getTmpplate(type) {
    this.emailTemp = type;
    const { admin_id } = this.global.admin || {};
    const displayToastNotification = (status: string, message: string) => {
      this.toast.toastNotification1(message, status);
    };
    switch (type) {
      case 'signature':
        this.emailServices.getSignature(admin_id).subscribe(({ success, data }) => {
          if (success) {
            if (data.length > 0) {
              this.signature = data;
            }
            else {
              displayToastNotification('Email Signature!', 'You have not created any signature yet');
            }
          }
        });
        break;
      case 'email':
        this.emailServices.getEmail().subscribe(({ success, templates }) => {
          if (success) {
            if (templates.length > 0) {
              this.emails = templates;
            }
            else {
              displayToastNotification('Email Template!', 'Email template not found');
            }
          }
        });
        break;
      default:
        break;
    }
  }


  onEmailTemplateSelectionChange(selectedTemplate: any) {
    console.log(selectedTemplate);
    if (selectedTemplate.value) {
      this.user.message = selectedTemplate.value.body.html
    }
  }

  onEmailSignatureSelectionChange(selectedTemplate: any) {
    console.log(selectedTemplate.value);
    if (selectedTemplate.value) {
      this.user.message = selectedTemplate.value.body

    }
  }


  // getting contractor
  getContractor_data() {
    this.server.getContractor()
      .subscribe(({ success, data }) => {
        if (success) {
          this.otherContractor = data;
        }
      });

    this.cus.getCust().subscribe(({ success, data }) => {
      if (success) {
        this.otherCustomers = data;
      }
    }
    );
  }


}
