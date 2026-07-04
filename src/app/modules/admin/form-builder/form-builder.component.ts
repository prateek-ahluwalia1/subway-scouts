import { Component, ElementRef, EventEmitter, OnInit, TemplateRef, ViewChild } from '@angular/core';
import { NgbModal, NgbModalRef, ModalDismissReasons, } from '@ng-bootstrap/ng-bootstrap';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';
import { FormBuildService } from 'app/services/form-build.service';
import { ToastServiceService } from 'app/services/toast-service.service';
import { ConfirmBoxInitializer, DialogLayoutDisplay, AppearanceAnimation, DisappearanceAnimation, ButtonMaker, ButtonLayoutDisplay } from '@costlydeveloper/ngx-awesome-popup';
import { StaffService } from 'app/services/staff.service';
import { Router } from '@angular/router';
import { CustomerService } from 'app/services/customer.service';
import { GlobalVariable } from 'app/shared/global';
import { PersonalRefrenceFormComponent } from '../personal-refrence-form/personal-refrence-form.component';
import { CheckListFormComponent } from '../check-list-form/check-list-form.component';
import { EmergencyContactComponent } from '../emergency-contact/emergency-contact.component';
import { EmployeeDetailsComponent } from '../employee-details/employee-details.component';
import { RefrencesFormComponent } from '../refrences-form/refrences-form.component';
import { UniformFormComponent } from '../uniform-form/uniform-form.component';
import { TrackAdminActivityService } from 'app/services/track-admin-activity.service';
import { PermissionsService } from 'app/services/permissions.service';
import { MatTabChangeEvent } from '@angular/material/tabs';
export class Customers {
  id: number;
  name: string;
}

interface Object {
  components: any[]; // Replace 'any' with a more specific type if possible
}

@Component({
  selector: 'app-form-builder',
  templateUrl: './form-builder.component.html',
  styleUrls: ['./form-builder.component.scss'],
})
export class FormBuilderComponent implements OnInit {

  private currentModal: NgbModalRef;
  createform: FormGroup;
  @ViewChild('formtemp') formtemp: any;

  searchform
  design
  FormList: any;
  searchTerm: string;
  form_id
  @ViewChild('json') jsonElement?: ElementRef;
  public form: Object = { components: [] };
  adminPermissions: any; z
  onChange(event) {
    console.log("Form", event.form);
    for (const component of this.form.components) {
      if (component.type === 'button') {
        console.log('Button Component:', component);
        // const { label, key, disableOnInvalid, input } = component;
        // console.log('Label:', label);
        // console.log('Key:', key);
        // console.log('Disable On Invalid:', disableOnInvalid);
        // console.log('Input:', input);
        // const buttonComponent = component;
        // console.log('Button Component:', buttonComponent);
      }
    }
  }


  adminList = [];

  Loginadminid;
  type;
  Login_admin_data;
  routeId
  id
  shareFormType
  constructor(private modalService: NgbModal, private fb: FormBuilder, private formService: FormBuildService,
    private toast: ToastServiceService, private router: Router,
    public userService: StaffService, private cus: CustomerService, private global: GlobalVariable,
    private trackAdmin: TrackAdminActivityService, private permissionService: PermissionsService) {
    this.createform = this.fb.group({
      name: ['', [Validators.required]],
    });

    let routerId = localStorage.getItem('routerId')
    if (!routerId) {
      this.activity()
    }
  }

  ngOnInit(): void {
    this.adminPermissions = this.permissionService.getPermissionsByTitle('Forms');
    if (this.adminPermissions && this.adminPermissions.actions) {
      this.adminPermissions.actionsObject = {};
      for (const action of this.adminPermissions.actions) {
        this.adminPermissions.actionsObject[action.title] = action.enabled;
      }
    }
    console.log(this.adminPermissions);

    this.getAllForms();

    this.cus.getCust().subscribe(res => {
      if (res.success == true) {
        this.customers = res.data
      }
    }, (error => {

    }))
  }

  ngOnDestroy() {
    localStorage.removeItem('routerId');

    this.trackAdmin.storeActivity('Exit Forms Page', 'Exit Forms Page', this.routeId).subscribe(res => {
    })
  }

  activity() {
    this.trackAdmin.storeActivity('Forms Page', 'Enter in Forms Page').subscribe(({ success, id }) => {
      if (success) {
        localStorage.setItem('routerId', id)
        this.routeId = id
      }
    })
  }

  close() {
    this.modalService.dismissAll()
  }

  openVerticallyCentered(content) {
    this.currentModal = this.modalService.open(content, { animation: true, centered: true });
  }

  openXl(formtemp) {
    if (this.currentModal) {
      this.currentModal.dismiss();
    }
    this.currentModal = this.modalService.open(formtemp, { size: 'xl' });
  }



  nameValue: string;
  status: string;
  onSubmit(status: any) {
    this.nameValue = this.createform.value.name;
    this.status = status;
    if (this.nameValue != '') {
      localStorage.setItem('nameValue', this.nameValue);
      this.openXl(this.formtemp);
    }
  }


  // Save forms
  save() {
    let params = {
      title: localStorage.getItem('nameValue'),
      body: this.form,
      type: this.status,
      admin_id: this.global.admin.admin_id
    } as { title: string, body: Object, type: string, id?: string, admin_id: number };

    if (this.id) {
      params.id = this.id;
    }

    if (!this.id) {
      this.formService.saveForm(params).subscribe(({ success, message }) => {
        if (success) {
          this.close()
          this.getAllForms();
          const status = 'Form Template Operation!';
          this.toast.toastNotification(message, status);
        }
      });
    }
    else {
      this.formService.updateForm(params).subscribe(({ success, message }) => {
        if (success) {
          this.close()
          this.getAllForms();
          const status = 'Form Template Operation!';
          this.toast.toastNotification(message, status);
        }
      });
    }


  }



  // Get all forms
  getAllForms() {
    this.formService.getAllForms().subscribe(({ success, data }) => {
      if (success) {
        this.FormList = data;
      }
    })
  }



  //delete forms by payload id
  deleteForm(id) {
    const newConfirmBox = new ConfirmBoxInitializer();
    newConfirmBox.setTitle('Confirm Action');
    newConfirmBox.setMessage('Are you sure to perform this action?');
    // Choose layout color type
    newConfirmBox.setConfig({
      layoutType: DialogLayoutDisplay.DANGER, // SUCCESS | INFO | NONE | DANGER | WARNING
      animationIn: AppearanceAnimation.SWING, // BOUNCE_IN | SWING | ZOOM_IN | ZOOM_IN_ROTATE | ELASTIC | JELLO | FADE_IN | SLIDE_IN_UP | SLIDE_IN_DOWN | SLIDE_IN_LEFT | SLIDE_IN_RIGHT | NONE
      animationOut: DisappearanceAnimation.BOUNCE_OUT, // BOUNCE_OUT | ZOOM_OUT | ZOOM_OUT_WIND | ZOOM_OUT_ROTATE | FLIP_OUT | SLIDE_OUT_UP | SLIDE_OUT_DOWN | SLIDE_OUT_LEFT | SLIDE_OUT_RIGHT | NONE
      allowHtmlMessage: true,
      buttonPosition: 'center', // optional 
    });
    newConfirmBox.setButtons([
      new ButtonMaker('Confirm', 'Confirm', ButtonLayoutDisplay.SUCCESS),
      new ButtonMaker('Discard', 'Discard', ButtonLayoutDisplay.DANGER),
    ]);
    // Simply open the popup and observe button click
    newConfirmBox.openConfirmBox$().subscribe(resp => {
      if (resp.clickedButtonID == 'Confirm') {
        this.formService.delDesign(id).subscribe(res => {
          if (res.success) {
            this.getAllForms()
            let status = 'Form Template Operation!'
            this.toast.toastNotification1(res.message, status)
          }
        })
      }
    });
  }

  formDatas
  viewForm(temp, formdraft) {
    this.nameValue = temp.title
    this.formDatas = JSON.parse(temp.body);
    console.log(this.nameValue, this.formDatas);

    this.modalService.open(formdraft);
  }

  showStaff(temp, guardlist) {
    this.shareFormType = 'custom'
    if (this.currentModal) {
      this.currentModal.dismiss();
    }
    this.form_id = temp.id;
    this.nameValue = temp.title;
    this.modalService.open(guardlist, { centered: true });
    // this.nameValue = temp.title
    // this.getGuards()
  }

  editForm(temp, formtemp) {
    this.id = temp.id
    this.form = JSON.parse(temp.body);
    this.currentModal = this.modalService.open(formtemp, { size: 'xl' });
    console.log(temp);
    this.status = temp.type
  }


  Send() {
    this.FormList.forEach(form => {
      console.log(form.id);
    });
  }



  showformPage(temp, form) {
    this.currentModal = this.modalService.open(form, { size: 'lg' });

    this.router.navigate(['/form', temp.id]);
    // const modalRef = this.modalService.open(FormComponent, { size: 'lg' });
    // modalRef.componentInstance.tempId = temp.id;
  }


  //Filters data



  selectedState = 'Victoria';
  states: string[] = ['Victoria', 'New South Wales', 'Tasmania', 'Queensland', 'Western Australia', 'South Australia'];

  customers: Customers[] = [];
  sitesList: any = [];
  guardList: any = [];

  data: any;
  getState(value) {
    this.selectedState = value;
  }


  customersIds: any[] = [];
  fromMultiCustomer: any = {};
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


  sitesIds;
  receiveDataFromChildSite(data: any) {
    this.sitesIds = data?.map(item => item.id);
    if (data) {
      this.formService.getSites(this.sitesIds, this.selectedState).subscribe(res => {
        if (res.success) {
          if (res.data) {
            res.data.forEach(element => {
              element.name = element.first_name + (element.middle_name ? ' ' + element.middle_name : '') + (element.last_name ? ' ' + element.last_name : '');
            });
          }

          this.guardList = res.data;
        }
      })
    }
  }

  guardsIds;
  fromMultiGuard;
  receiveDataFromGuards(data: any) {
    this.fromMultiGuard = data;
    console.log("Guards data", this.fromMultiGuard);
    this.guardsIds = this.fromMultiGuard.value.map(item => item.id);
    localStorage.setItem('guardsIds', this.guardsIds);
    console.log("Guards Ids", this.guardsIds)
  }

  SendEmail() {
    let data = {
      type: this.shareFormType,
      guard_id: this.guardsIds,
      url: 'form',
      form_id: this.form_id,
    }
    this.formService.getguards(data).subscribe(res => {
      if (res.success) {
        this.close()
        this.toast.toastNotification('Form has been successfully sent', 'Form Sent!')
      }
    })
  }

  showbuiltPage(type, url?) {
    const componentMapping = {
      'Checklist Form': CheckListFormComponent,
      'Employee Details Form': EmployeeDetailsComponent,
      'Uniform Form': UniformFormComponent,
      'Emergency Contact Form': EmergencyContactComponent,
      'Reference Form': RefrencesFormComponent,
      'Personal Reference Form': PersonalRefrenceFormComponent
    };

    this.router.navigate([url]);
  }

  formtype
  shreBuiltPage(type, guardlist?, url?) {
    this.shareFormType = 'builtin'

    if (this.currentModal) {
      this.currentModal.dismiss();
    }
    this.form_id = url;
    this.nameValue = type;
    this.modalService.open(guardlist, { centered: true });
    // this.nameValue = temp.title
    // this.getGuards()
  }

  selectedTabLabel: string = 'Custom Form';
  onTabChange(event: number): void {
    this.selectedTabLabel = event === 0 ? 'Custom Form' : 'Built-in Forms';
  }

  clickedHistory: any = [];
  history: any = []
  viewList(type, guardForm?, url?) {
    this.shareFormType = 'builtin';
    let data = {
      form_id: "employee-details"
    }
    this.formService.GetGuardInfo(data).subscribe(res => {
      if (res.success) {
        this.clickedHistory = res.clickedForm;
        this.history = res.history;
      }
    })

    this.currentModal = this.modalService.open(guardForm, { size: 'lg' });
  }

}
