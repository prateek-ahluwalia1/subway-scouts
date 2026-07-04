import { AddUniformDetailComponent } from './../add-uniform-detail/add-uniform-detail.component';
import { GlobalVariable } from "app/shared/global";
import { Component, Input, OnInit } from "@angular/core";
import { NgbModal, ModalDismissReasons, NgbModalRef, } from "@ng-bootstrap/ng-bootstrap";
import moment from 'moment';
import { PersonDetailComponent } from "../person-detail/person-detail.component";
import { AddDocumentComponent } from "../add-document/add-document.component";
import { EmploymentDetailComponent } from "../employment-detail/employment-detail.component";
import { DetailModelComponent } from "../detail-model/detail-model.component";
import { StaffService } from "app/services/staff.service";
import { ToastServiceService } from "app/services/toast-service.service";
import { AppearanceAnimation, ConfirmBoxInitializer, DialogLayoutDisplay, DisappearanceAnimation } from "@costlydeveloper/ngx-awesome-popup";
import { FormArray, FormBuilder, FormGroup, Validators } from "@angular/forms";
import { CustomerService } from "app/services/customer.service";
import { faTrash } from '@fortawesome/free-solid-svg-icons';
import { AddUnavailabilityComponent } from '../add-unavailability/add-unavailability.component';
import { ServiceService } from 'app/services/service.service';
import { PermissionsService } from 'app/services/permissions.service';
import { Observable } from 'rxjs';
import { formatPercent } from '@angular/common';

export interface PeriodicElement {
  Action: string;
  Performed_By: string;
}

export class Sites {
  id: number;
  site_name: string;
}

const ELEMENT_DATA: PeriodicElement[] = [];

@Component({
  selector: 'app-user-menu',
  templateUrl: './user-menu.component.html',
  styleUrls: ['./user-menu.component.scss']
})

export class UserMenuComponent implements OnInit {

  @Input() fromParent;
  addDocChild: any
  documentList = []
  selectedtypeId: any = 1;
  selectedtypeName: any = "Personal";
  showrates: boolean = false;
  documentsList: any = [];
  closeAdminCreateModal: NgbModalRef;
  action: any;
  modelData: string;
  uploadedImage: boolean = false;
  end_time
  createDetailModel: NgbModalRef;
  createPersonalModal: NgbModalRef;
  createEmploymentModal: NgbModalRef;
  todayDate: string;
  array: any = [];
  employee_ment_data
  displayedColumns: string[] = ['Action', 'Performed_By'];
  dataSource = ELEMENT_DATA;
  selectedAvailablity: void;
  selectedAvailablityIndex: any;
  selectedDay: any;
  addStaffIds: FormGroup;
  addTrainedsites: FormGroup;
  uniformList: any = []
  faTrash = faTrash
  foods = [
    { value: 'full time', viewValue: 'Full Time' },
    { value: 'others', viewValue: 'Others' },
  ];
  staffAvailbility: any = []
  staffActivity: any[] = [];
  upCommingShifts: any[] = [];
  previousShifts: any;
  sites: { [key: number]: any[] } = {};
  stafffPermissions: any;
  leaveForm: FormGroup;
  guardDocumentType: any;
  customers: any = [];
  staffLeaves;

  constructor(public globals: GlobalVariable, private modalService: NgbModal,
    private staffService: StaffService, private toast: ToastServiceService, private fb: FormBuilder,
    private cus: CustomerService, private service: ServiceService, private permissionService: PermissionsService,
    private staffservice: StaffService) {
    const per = this.permissionService.getPermissionsByTitle('Onboarding');
    this.stafffPermissions = per?.childPage?.find(item => item.title === 'Other Staff');
    this.todayDate = moment().format('DD/MM/YYYY HH:MM');
  }

  ngOnInit(): void {
    // console.log('here from parent',this.fromParent);
    this.addStaffIds = this.fb.group({
      internal_id: [''],
      guard_external_ids: this.fb.array([
      ]),
    })

    this.addTrainedsites = this.fb.group({
      guard_trained_on_sites: this.fb.array([])
    });

    this.getCustomer();
    this.initializeLeaveform()
    this.patchLeaves()
  }

  tabs = [
    {
      name: "PROFILE",
      types: [
        {
          type: "Personal",
          id: 1,
        },
        {
          type: "Employment",
          id: 2,
        },
        {
          type: "Documents",
          id: 3,
        },
        {
          type: "Staff Uniform",
          id: 9,
        },
        {
          type: "ID",
          id: 8,
        },
      ],
    },

    {
      name: "SCHEDULING",
      types: [
        {
          type: "Shifts",
          id: 4,
        },
        {
          type: "Leaves",
          id: 5,
        },
        {
          type: "Availability",
          id: 6,
        },
      ],
    },

    {
      name: "ACTIVITY",
      types: [
        {
          type: "Profile Tracker",
          id: 7,
        },
        {
          type: "Trained Locations",
          id: 10,
        },
      ],
    },
  ];

  recordShown() { }
  recordHidden() { }
  onClick(i) {
    this.selectedtypeId = i.id;
    this.selectedtypeName = i.type;
    if (i.type == 'Employment') {
      this.staffService.getStaffDocument(this.fromParent.id).subscribe(res => {
        if (res.success) {
          this.employee_ment_data = res.data
        }
      }, (error => {
      }))
    }
    if (i.type == 'Documents') {
      this.getDocData()
    }
    if (i.type == 'ID') {
      this.getStaffIds()
    }
    if (i.type == 'Staff Uniform') {
      this.getStaffUniforms();
    }
    if (i.type == 'Profile Tracker') {
      this.getProfileTrack();
    }
    if (i.type == 'Shifts') {
      this.getAllShifts();
    }
    if (i.id == 6) {
      this.getStaffAvailbility();
    }
    if (i.id == 10) {
      this.getSites();
    }
    if (i.id == 5) {
      this.getLeaves();
    }
  }

  getTime(e) {}

  toggle(e) {}

  showRates() {
    this.showrates = !this.showrates;
  }

  dismiss(data?) {
    this.modalService.dismissAll(data);
  }

  openPdf(file) {
    if (file) {
      window.open(file, '_blank');
    }
    else {
      return
    }
  }

  openDocument(doc) {
    this.closeAdminCreateModal = this.modalService.open(AddDocumentComponent, {
      windowClass:
        "add-staff-document-class",
      animation: true,
    });
    this.closeAdminCreateModal.componentInstance.fromUserMenu = this.fromParent;
    this.closeAdminCreateModal.componentInstance.DocList = doc;
    this.closeAdminCreateModal.result.then(
      (result) => {
        if (result == 'document') {
          this.getDocData()
        }
      },
      (reason) => {
        console.log(
          "Modal Closed Reason -> ",
          `Dismissed ${this.getDismissReason(reason)}`
        );
      }
    );
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

  openDetailModel(type, index, documentDetail) {  /// sab uploaded documents ko reset kr dyga 
    this.createDetailModel = this.modalService.open(DetailModelComponent, {
      windowClass:
        "detailModel",
    });

    let document = {
      index: index,
      document: documentDetail ? documentDetail : '',
      modelType: type
    }
    this.createDetailModel.componentInstance.document = document;
    this.createDetailModel.result.then(
      (result) => {
        console.log("Modal Result", `Closed with: ${JSON.parse(result)}`);
      },
      (reason) => {
        console.log(
          "Modal Closed Reason -> ",
          `Dismissed ${this.getDismissReason(reason)}`
        );
      }
    );
  }

  sendNotification() {}

  getStaffUniforms() {
    this.staffService.getUniforms(this.fromParent.id)
    .subscribe(res => {
      if (res.success == true) {
        this.uniformList = res.data;
      }
    });
  }

  // open_PersonalDetail_Model

  open_PersonalDetail_Model(id?) {

    this.createPersonalModal = this.modalService.open(PersonDetailComponent, {
      windowClass: "personalDetailOpenModalClass ",
      size: "xl",
      animation: false,
    });
    this.createPersonalModal.componentInstance.userData = this.fromParent;
    this.createPersonalModal.componentInstance.type = id;
    this.createPersonalModal.result.then(
      (result) => {
        console.log("Modal Result", `Closed with: ${result}`);
      },
      (reason) => {
        let rea = this.getDismissReason(reason)
        if (rea == 'QuickStaff') {
          this.dismiss('update')
          this.globals.selectedCustomers = []
        }
      }
    );
  }

  openUniform(uniformId?) {
    this.closeAdminCreateModal = this.modalService.open(AddUniformDetailComponent, {
      animation: true,
      //  fullscreen:true,
      size: "xl",
      windowClass: 'addUniformClass'

    });
    this.closeAdminCreateModal.componentInstance.fromUserMenu = this.fromParent;
    this.closeAdminCreateModal.componentInstance.uniformId = uniformId;
    this.closeAdminCreateModal.result.then(
      (result) => {
        console.log("Modal Result", `Closed with: ${result}`)
      },
      (reason) => {
        console.log(
          "Modal Closed Reason -> ",
          `Dismissed ${this.getDismissReason(reason)}`
        );
        if (reason === 'uniform') {
          this.getStaffUniforms();
        }
      }
    );
  }

  cusName(customerIdArray: { name: string }[]): string {
    if (customerIdArray) {
      const customerNames = customerIdArray.map((customer) => customer.name);
      return customerNames.join(', ');
    }
  }

  openUnavailability(value?) {
    const modalRef = this.modalService.open(AddUnavailabilityComponent, {
      windowClass:
        "addUnavailabilityClass ",
      size: 'xl',
      animation: true,
    });
    modalRef.componentInstance.fromParentUserMenu = this.fromParent.id;
    modalRef.componentInstance.data = value || this.staffAvailbility;
    modalRef.result.then(
      (result) => {
        console.log("Modal Result", `Closed with: ${result}`);

      },
      (reason) => {
        if (reason == 'submitted') {
          this.getStaffAvailbility()
        }
        console.log(
          "Modal Closed Reason -> ",
          `Dismissed ${this.getDismissReason(reason)}`
        );
      }
    );
  }

  // open_PersonalDetail_Model
  open_EmploymentDetail_Model() {

    const modalRef = this.modalService.open(EmploymentDetailComponent, {
      windowClass:
        "employmentDetailOpenModalClass ",
      size: 'xl',
      animation: false,
    });
    modalRef.componentInstance.fromParentUserMenu = this.fromParent;
    modalRef.componentInstance.fromParentEmployeeDetail = this.employee_ment_data;
    modalRef.result.then(
      (result) => {
        console.log("Modal Result", `Closed with: ${result}`);
      },
      (reason) => {
        let rea = this.getDismissReason(reason)
        if (rea == 'QuickStaff') {
          this.dismiss('update')
        }
        console.log(
          "Modal Closed Reason -> ",
          `Dismissed ${this.getDismissReason(reason)}`
        );
      }
    );
  }

  ///open file in new tab
  openFile(url) {
    window.open(url, '_blank');
  }

  /////Get Staff Doc Tab3 data
  getDocData() {
    this.staffService.getStaffAddDocument(this.fromParent.id).subscribe(res => {
      if (res.success) {
        this.documentList = res.data;
      }
    }, (error => {}))
  }

  ////Edit tab3 Doc
  openAddDocModel(document, view?) {

    this.closeAdminCreateModal = this.modalService.open(AddDocumentComponent, {
      windowClass:
        "add-staff-document-class",
      animation: true,
    });
    this.closeAdminCreateModal.componentInstance.documentList = document;
    this.closeAdminCreateModal.componentInstance.fromUserMenu = this.fromParent;

    if (view == 'view') {
      this.globals.documentViewEnable = true;
    }

    this.closeAdminCreateModal.result.then(
      (result) => {
        this.globals.documentViewEnable = false;
        console.log("Modal Result", `Closed with: ${result}`)
        if (result == 'document') {
          this.getDocData()
        }
      },
      (reason) => {
        this.globals.documentViewEnable = false;
        console.log(
          "Modal Closed Reason -> ",
          `Dismissed ${this.getDismissReason(reason)}`
        );
      }
    );
  }

  ////delete tab3 specific Document
  delDocument(id) {
    this.openConfirmBox(id)
  }

  // standard typescript method.
  openConfirmBox(id) {
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
    newConfirmBox.openConfirmBox$().subscribe(resp => {
      if (resp.success) {
        this.staffService.delAddDoc(id).subscribe(res => {
          if (res.success) {
            this.getDocData()
            let status = 'Staff Document Operation!'
            this.toast.toastNotification(res.message, status)
          }
        }, (error) => {});
      }
    });
  }

  getCustomer() {
    this.cus.getCust().subscribe(({ success, data }) => {
      if (success) {
        this.customers = data;
      }
    }, (error => {}))
  }

  //Add id's
  removeTextArea(value, index) {
    if (value.id) {
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
      newConfirmBox.openConfirmBox$().subscribe(resp => {
        if (resp.success) {
          this.staffService.delStaffIds(value.id).subscribe(({ success, message }) => {
            if (success) {
              (this.addStaffIds.get('guard_external_ids') as FormArray).removeAt(index);
              let status = "Staff ID's Operation!"
              this.toast.toastNotification1(message, status)
            }

          });

        }
      });
    }
    else {
      (this.addStaffIds.get('guard_external_ids') as FormArray).removeAt(index);
    }
  }

  get guard_external_ids(): FormArray {
    return this.addStaffIds.get('guard_external_ids') as FormArray;
  }

  addTask() {
    this.guard_external_ids.push(this.fb.group({
      id: [''],
      customer_id: [''],
      external_id: [''],
    }));
  }

  saveIds() {
    if (this.fromParent) {
      this.addStaffIds.value.guard_id = this.fromParent.id
      this.addStaffIds.value.admin_id = this.globals.admin.admin_id
    }
    this.staffService.saveIds(this.addStaffIds.value).subscribe(({ success, message }) => {
      let status = "Staff Internal & Extenal ID's"
      this.toast.toastNotification(message, status)
    })
  }

  getStaffIds() {
    this.staffService.getStaffIds(this.fromParent.id).subscribe(({ success, data }) => {
      if (success) {
        this.addStaffIds.get('internal_id').setValue(data.internal_id)
        if (data.guard_external_ids) {
          while (this.guard_external_ids.length > 0) {
            this.guard_external_ids.removeAt(0);
          }
          data.guard_external_ids.forEach(element => {
            this.guard_external_ids.push(this.fb.group({
              id: [element.id],
              customer_id: [element.customer_id],
              external_id: [element.external_id],
            }));
          });
        }
      }
    }, (error => {}))
  }

  getProfileTrack() {
    this.staffService.getProfileTracker(this.fromParent.id)
    .subscribe(res => {
      this.staffActivity = res.data;
    });
  }

  getAllShifts() {
    this.staffService.getShiftRecords(this.fromParent.id)
    .subscribe(res => {
      this.upCommingShifts = res.next;
      this.previousShifts = res.pervious;
    });
  }

  getStaffAvailbility() {
    let value = {
      guard_id: this.fromParent.id,
    }
    this.staffService.getStaffAvailbility(value).subscribe(({ success, data }) => {
      if (success) {
        this.staffAvailbility = data;
      }
    })
  }

  // staff location trained start here
  get guard_trained_on_sites() {
    return this.addTrainedsites.get('guard_trained_on_sites') as FormArray;
  }

  addSite() {
    this.guard_trained_on_sites.push(this.fb.group({
      id: [''],
      customer_id: [''],
      site_id: [''],
      status: [''],
      guard_id: this.fromParent.id,
    }));
  }

  removeSite(value, index) {
    if (value.id) {
      const newConfirmBox = new ConfirmBoxInitializer();
      newConfirmBox.setTitle('Confirm Action');
      newConfirmBox.setMessage('Are you sure you want to confirm this action?');
      newConfirmBox.setConfig({
        layoutType: DialogLayoutDisplay.SUCCESS,
        animationIn: AppearanceAnimation.SWING,
        animationOut: DisappearanceAnimation.BOUNCE_OUT,
        allowHtmlMessage: true,
        buttonPosition: 'right',
      });

      newConfirmBox.setButtonLabels('Confirm', 'Decline');
      newConfirmBox.openConfirmBox$().subscribe(resp => {
        if (resp.success) {
          this.staffService.delSites(value.id).subscribe(({ success, message }) => {
            if (success) {
              this.guard_trained_on_sites.removeAt(index);
              let status = "Trained Location Operation!";
              this.toast.toastNotification1(message, status);
              
            }
          });
        }
      });
    } else {
      this.guard_trained_on_sites.removeAt(index);
    }
  }

  onCustomerSelect(event: any, index: number) {
    const selectedCustomerId = event.value;
    this.guard_trained_on_sites.at(index).get('customer_id').setValue(selectedCustomerId);
    if (!this.sites[selectedCustomerId]) {
      this.service.getMutilCusMutilSite({ customer_id: [selectedCustomerId] }).subscribe(({ success, data }) => {
        if (success) {
          this.sites[selectedCustomerId] = data;
          this.guard_trained_on_sites.at(index).get('site_id').setValue(null); 
        }
      }, error => {
        console.log(error);
      });
    } else {
      this.guard_trained_on_sites.at(index).get('site_id').setValue(null);
    }
  }

  saveSites() {
    if (this.fromParent) {
      this.addTrainedsites.value.admin_id = this.globals.admin.admin_id;
    }
    const formData = this.addTrainedsites.value;
    this.staffService.saveSites(formData).subscribe(({ success, message }) => {
      let status = "Trained Locations";
      this.toast.toastNotification(message, status);
      this.dismiss('update');
    });
  }

  getSites() {
    this.staffService.getSites(this.fromParent.id).subscribe(({ success, data }) => {
      if (success) {
        while (this.guard_trained_on_sites.length > 0) {
          this.guard_trained_on_sites.removeAt(0);
        }
        data.forEach((element) => {
          const customerId = element.customer_id;
          if (!this.sites[customerId]) {
            this.sites[customerId] = [];
          }
          
          this.service.getMutilCusMutilSite({ customer_id: [customerId] }).subscribe(({ success, data }) => {
            if (success) {
              this.sites[customerId] = data;
            }
          });

          this.guard_trained_on_sites.push(this.fb.group({
            id: [element.id],
            customer_id: [element.customer_id],
            site_id: [element.site_id],
            status: [element.status],
            guard_id: [element.guard_id]
          }));
        });
      }
    }, error => {
      console.log(error);
    });
  }

  getSelectedValue(id, index) {
    if (id) {
      this.staffService.getCusSite(id).subscribe(
        ({ success, data }) => {
          if (success) {
            if (data.length != 0) {
              this.sites[index] = data;
            }
          }
        },
        (error) => {
          console.log(error);
        }
      );
    }
  }

  getLeaves() {
    this.staffService.getStaffLeaves(this.fromParent.id)
    .subscribe(res => {
      this.staffLeaves = res.data;
    });
  }

  filteredData(data: any): any {
    if (!data) {
      return {};
    }
    const excludedProperties = ['customer_id', 'contractor_id', 'updated_at'];

    return Object.keys(data)
    .filter(key => !excludedProperties.includes(key))
    .reduce((obj, key) => {
      obj[key] = data[key];
      return obj;
    }, {});
  }

  viewFile(url) {
    window.open(url, '_blank')
  }

  initializeLeaveform() {
    this.leaveForm = this.fb.group({
      annual_leave: ['', Validators.min(0)],
      sick_leave: ['', Validators.min(0)]
    })
  }

  getleave() {
    console.log(this.fromParent);
    this.markFormControlsAsTouched(this.leaveForm);
    if (this.leaveForm.invalid) {
      this.toast.toastNotification1('Please check error on field and fill this field', 'Invalid Form!');
      return;
    }
    this.leaveForm.value.id = this.fromParent.id

    this.staffService.updateLeave(this.leaveForm.value).subscribe((res) => {
      if (res.success) {
        let status = 'Success'
        this.toast.toastNotification(res.message + ' Successfully.', status)
      }
    })
  }

  markFormControlsAsTouched(formGroup: FormGroup) {
    Object.values(formGroup.controls).forEach((control) => control.markAsTouched());
  }

  patchLeaves() {
    const { annual_leave, sick_leave } = this.fromParent;
    this.leaveForm.patchValue({
      annual_leave: annual_leave,
      sick_leave: sick_leave
    });
  }

}
