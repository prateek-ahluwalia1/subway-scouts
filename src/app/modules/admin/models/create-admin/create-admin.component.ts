import { NgbActiveModal } from '@ng-bootstrap/ng-bootstrap';
import { ChangeDetectionStrategy, ChangeDetectorRef, Component, Input, OnInit } from '@angular/core';
import { AbstractControl, FormBuilder, FormControl, FormGroup, ValidationErrors, ValidatorFn, Validators } from '@angular/forms';
import { AdminService } from 'app/services/admin.service';
import { CustomerService } from 'app/services/customer.service';
import { StaffService } from 'app/services/staff.service';
import { GlobalVariable } from 'app/shared/global';
import { ToastServiceService } from 'app/services/toast-service.service';
import { NgxSpinnerService } from 'ngx-spinner';
import { RolePermissionService } from 'app/services/role-permission.service';
import { TrackAdminActivityService } from 'app/services/track-admin-activity.service';
export class Customers {
  id: number;
  name: string;
}
export class Sites {
  id: number;
  site_name: string;
}

@Component({
  selector: 'app-create-admin',
  templateUrl: './create-admin.component.html',
  styleUrls: ['./create-admin.component.scss'],
  changeDetection: ChangeDetectionStrategy.OnPush
})
export class CreateAdminComponent implements OnInit {

  @Input() status;
  states = new FormControl('');
  tabType = 'Personal details';
  customer = new FormControl('');
  submitted = false;
  userForm: FormGroup
  @Input() fromParent;
  @Input() fromParentedit;
  @Input() fromSalesPersonSection: boolean = false;
  selectedState
  adminStatus
  type = 'create-admin'
  statesList: string[] = ['Victoria', 'New South Wales', 'Tasmania', 'Queensland', 'Western Australia', 'South Australia'];
  roles: any = [];

  selectedSites
  sitesList = []
  base64String: string;
  selectedfile: string;

  customersIds
  fromMultiCustomer
  receiveDataFromChild(data: string) {
    this.fromMultiCustomer = data;
    this.customersIds = this.fromMultiCustomer.value.map(item => item.id);
    if (this.fromMultiCustomer) {
      this.getSites()
    }
  }

  sitesIds
  receiveDataFromChildSite(data: any) {
    this.sitesIds = data?.map(item => item.id);
  }
  constructor(public formBuilder: FormBuilder, public ngbActiveModal: NgbActiveModal,
    public adminservice: AdminService, private cus: CustomerService, private staff: StaffService,
    private roleService: RolePermissionService, private trackAdmin: TrackAdminActivityService, private cdr: ChangeDetectorRef,
    public global: GlobalVariable, private toastService: ToastServiceService, private spinner: NgxSpinnerService) {
    this.getRoles()
    if (!this.fromSalesPersonSection) {
      this.cus.getCust().subscribe(res => {
        if (res.success) {
          this.customers = res.data
          this.cdr.markForCheck()
        }
      }, (error => {

      }))
    }

  }

  customers: Customers[] = [];
  ngOnInit(): void {
    if (this.fromParent != 'new') {
      this.spinner.show()
      this.global.selectedCustomers = this.fromParent.data.specific_customer
      this.global.selectedSite = this.fromParent.data.specific_sites
      this.userForm = this.formBuilder.group({
        name: [this.fromParent.data.name, Validators.required],
        email: [this.fromParent.data.email],
        state: [this.fromParent.data.state],
        status: [this.fromParent.data.status],
        phone: [this.fromParent.data.phone],
        userType: [this.fromParent.data.userType],
        id: [this.fromParent.data.id],
        temp_img: [this.fromParent.data.image],
        password: ['', this.fromParent != 'new' ? [this.passwordComplexityValidator()] : []],
        c_password: [''],
        role_id: [this.fromParent.data.role_id],
      },
        {
          validators: this.passwordMatchValidator
        });
      this.selectedfile = this.fromParent.data.image ? this.fromParent.data.image : ''
      this.spinner.hide()
      this.cdr.markForCheck()

    }
    else {
      this.userForm = this.formBuilder.group({
        name: ['', [Validators.required]],
        email: ['', [Validators.required, Validators.email]],
        state: ['',],
        status: ['', Validators.required],
        userType: [''],
        temp_img: [''],
        phone: ['', Validators.required],
        password: ['', [Validators.required, Validators.minLength(8), this.passwordComplexityValidator()]],
        c_password: ['', Validators.required],
        id: [''],
        role_id: ['', Validators.required],
      }, {
        validators: this.passwordMatchValidator
      });
    }



    if (this.fromSalesPersonSection) {
      this.userForm.get('userType').setValue('saleperson')
    }
    else {
      this.userForm.get('userType').setValue('admin')
    }

    this.cdr.markForCheck()
  }

  get f(): { [key: string]: AbstractControl } {
    return this.userForm.controls;
  }


  passwordMatchValidator: ValidatorFn = (control: AbstractControl): ValidationErrors | null => {
    const newPassword = control.get('password');
    const c_password = control.get('c_password');
    if (newPassword && c_password && newPassword.value !== c_password.value) {
      c_password.setErrors({ passwordMismatch: true });
      return { passwordMismatch: true };
    }
    return null;
  };

  markFormControlsAsTouched(formGroup: FormGroup) {
    Object.values(formGroup.controls).forEach((control) => control.markAsTouched());
  }

  onFormSubmit(value) {
    this.submitted = true;
    this.markFormControlsAsTouched(this.userForm);

    if (this.userForm.invalid) {
      return;
    }
    else {
      this.spinner.show()
      const obj = this.roles?.find(item => item.id == value.role_id)
      value.role_id = obj?.id
      value.specific_sites = this.sitesIds ? this.sitesIds : ''
      value.specific_customer = this.customersIds ? this.customersIds : ''
      this.adminservice.createAdmin(value).subscribe(res => {
        let status = `${this.fromSalesPersonSection ? 'Saleperson' : 'Admin'} Operation!`
        if (res.success) {
          this.userForm.reset()
          this.toastService.toastNotification(res.message, status)
          this.close('updated')
          this.spinner.hide()
          this.trackAdmin.storeActivity(`${this.fromSalesPersonSection ? 'Saleperson' : 'Admin'}`, `${this.fromParent == 'new' ? 'Added' : 'Updated'} a  ${this.fromSalesPersonSection ? 'Saleperson' : 'Admin'} with name: ${value?.name}`, localStorage.getItem('routerId')).subscribe(({ success, id }) => {
          })
        }
        else {
          this.toastService.toastNotification1(res[0], status)
          this.spinner.hide()
        }
        this.cdr.markForCheck()
      }, (error) => {
        this.spinner.hide()
        this.toastService.toastNotification1(error?.error?.message, 'Admin Operation!')
      });
    }
  }

  close(data?) {
    this.ngbActiveModal.dismiss(data);
  }

  uploadImage() {
    this.base64String = '';
    let input = document.createElement("input");
    input.type = "file";
    input.accept = "image/jpeg, image/png, image/jpg"; // only allow jpg and png files
    input.onchange = (_) => {
      let files = Array.from(input.files);
      const file = files[0];
      // encode the file using the FileReader API
      const reader = new FileReader();
      reader.onloadend = () => {
        this.base64String = (<string>reader.result).split(",")[1];
        this.selectedfile = "data:image/jpeg;base64," + this.base64String;
        this.adminservice.uploadImage(this.base64String, 'admin').subscribe(res => {
          if (res.success == true) {
            this.userForm.get('temp_img').setValue(res.path)
            this.cdr.markForCheck()
          }
        }, (error) => {
          console.log(error);

        });
      };
      reader.readAsDataURL(file);
    };
    input.click();
  }

  removeAvatar() {
    this.selectedfile = "";
    this.userForm.get('temp_img').setValue('')
  }


  getSites() {
    this.staff.getCusSite(this.customersIds).subscribe(res => {
      if (res.success) {
        this.sitesList = res.data
        this.cdr.markForCheck()
      }
    })
  }

  getRoles() {
    this.roleService.getAllRoleAndPermission().subscribe(({ data, success }) => {
      if (success) {
        this.roles = data?.map(item => ({ id: item.id, role: item.role }));
        this.cdr.markForCheck()
      }
    });
  }

  ngOnDestroy() {
    this.global.selectedCustomers = []
    this.global.selectedSite = []
  }


  // Add this method inside your component class
  passwordComplexityValidator(): ValidatorFn {
    return (control: AbstractControl): ValidationErrors | null => {
      const value = control.value;
      if (!value) {
        return null; // Don't validate empty value
      }
      const hasUpperCase = /[A-Z]+/.test(value);
      const hasSpecialChar = /[\W_]+/.test(value);
      const hasNumeric = /[0-9]+/.test(value);
      const isValid = hasUpperCase && hasSpecialChar && hasNumeric;
      return !isValid ? { passwordComplexity: true } : null;
    };
  }

}
