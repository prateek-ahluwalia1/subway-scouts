import { ChangeDetectionStrategy, ChangeDetectorRef, Component, Input, OnInit } from '@angular/core';
import { AbstractControl, FormControl, FormGroup, ValidationErrors, ValidatorFn, Validators } from '@angular/forms';
import { NgbActiveModal } from '@ng-bootstrap/ng-bootstrap';
import { ContractorService } from 'app/services/contractor.service';
import { CustomerService } from 'app/services/customer.service';
import { PermissionsService } from 'app/services/permissions.service';
import { ToastServiceService } from 'app/services/toast-service.service';
import { NgxSpinnerService } from 'ngx-spinner';
declare var google

@Component({
  selector: 'app-create-customer',
  templateUrl: './create-customer.component.html',
  styleUrls: ['./create-customer.component.scss'],
  changeDetection: ChangeDetectionStrategy.OnPush
})
export class CreateCustomerComponent implements OnInit {

  tabType = 'Personal details';
  addCustomr: FormGroup
  formBuilder: any;
  submitted = false;
  @Input() fromParent;
  @Input() fromParentedit;
  @Input() updateType;
  permissions
  userType = ''
  statesList = ["Victoria", "New South Wales", "Tasmania", "Queensland", "Western Australia", "South Australia"];

  constructor(private ngbActiveModal: NgbActiveModal, private customer: CustomerService, private spinner: NgxSpinnerService,
    private contracotr: ContractorService, private toast: ToastServiceService, private permissionService: PermissionsService,
    private cdr: ChangeDetectorRef) { }

  ngOnInit(): void {

    const per = this.permissionService.getPermissionsByTitle('Onboarding');

    if (this.fromParent == 'createContractor') {
      this.permissions = per?.childPage?.find(item => item.title === 'Contractors');
      this.userType = 'Contractor'
    }

    else if (this.fromParent == 'createCustomer') {
      this.permissions = per?.childPage?.find(item => item.title === 'Customers');
      this.userType = 'Customer'
    }

    if (this.updateType == 'updateContractor' && this.fromParent) {
      this.permissions = per?.childPage?.find(item => item.title === 'Contractors');
      this.addCustomr = new FormGroup({
        name: new FormControl(this.fromParent.data.name),
        email: new FormControl(this.fromParent.data.email),
        phone: new FormControl(this.fromParent.data.phone),
        address: new FormControl(this.fromParent.data.address),
        city: new FormControl(this.fromParent.data.city),
        state: new FormControl(this.fromParent.data.state),
        postal_code: new FormControl(this.fromParent.data.postal_code),
        status: new FormControl(this.fromParent.data.status),
        // password: new FormControl('', [this.passwordComplexityValidator()]),
        // c_password: new FormControl(''),
      },
        // {
        //   validators: this.passwordMatchValidator
        // }
      );
    }

    else if (this.updateType == 'updateCustomer' && this.fromParent) {
      this.permissions = per?.childPage?.find(item => item.title === 'Customers');
      this.addCustomr = new FormGroup({
        name: new FormControl(this.fromParent.data.name),
        email: new FormControl(this.fromParent.data.email),
        phone: new FormControl(this.fromParent.data.phone),
        address: new FormControl(this.fromParent.data.address),
        city: new FormControl(this.fromParent.data.city),
        state: new FormControl(this.fromParent.data.state),
        postal_code: new FormControl(this.fromParent.data.postal_code),
        status: new FormControl(this.fromParent.data.status),
        // password: new FormControl('', [this.passwordComplexityValidator()]),
        // c_password: new FormControl(''),
      },
        // {
        //   validators: this.passwordMatchValidator
        // }
      );
    }

    else {
      this.addCustomr = new FormGroup({
        name: new FormControl('', [Validators.required, this.alphabetValidator]),
        phone: new FormControl('', [Validators.required, this.validatePhone]),
        email: new FormControl('', [Validators.required, Validators.email]),
        address: new FormControl('', [Validators.required, this.customAddressValidator()]),
        city: new FormControl(''),
        state: new FormControl(''),
        postal_code: new FormControl(''),
        status: new FormControl('', [Validators.required]),
        // password: new FormControl('', [Validators.required, Validators.minLength(8), this.passwordComplexityValidator()]),
        // c_password: new FormControl('', [Validators.required]),
      },
        // {
        //   validators: this.passwordMatchValidator
        // }
      );
    }
    this.cdr.markForCheck()
  }

  get f(): { [key: string]: AbstractControl } {
    return this.addCustomr.controls;
  }

  alphabetValidator(control: AbstractControl): ValidationErrors | null {
    const value = control.value;
    if (value && !/^[A-Za-z][A-Za-z0-9\s]*$/.test(value)) {
      return { alphabet: true };
    }
    return null;
  }

  validatePhone(control: FormControl) {
    const pattern = /^(?:\+?61|0)(?:2|3|4|7|8)\d{8}$/;
    if (control.value && !pattern.test(control.value)) {
      return { invalidPhone: true };
    }
    return null;
  }

  customAddressValidator(): ValidatorFn {
    return (control: FormControl): { [key: string]: any } | null => {
      const value = control.value;
      if (!value) {
        return null;
      }
      const onlySpecialCharacters = /^[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]*$/.test(value);
      const onlyDigits = /^\d+$/.test(value);
      const onlyLetters = /^[a-zA-Z\s]*$/.test(value);
      if (onlySpecialCharacters || onlyDigits || onlyLetters) {
        return { invalidAddress: true };
      }
      return null;
    };
  }

  onFormSubmit(value) {
    this.spinner.show()
    this.submitted = true;
    if (this.addCustomr.invalid) {
      this.spinner.hide()
      return;
    }
    if (this.updateType && this.updateType == 'updateContractor') {
      value.userType = 'contractor'
      value.id = this.fromParent.data.id
      this.contracotr.createContractor(value).subscribe(res => {
        if (res.success) {
          this.close('update')
        }
        else {
          this.toast.toastNotification1(res.message, 'Resolve Errors')
        }
        this.spinner.hide()
      }, (error) => {
        this.toast.toastNotification1(error.error.message, 'Resolve Errors')
        this.spinner.hide()
      });
    }

    else if (this.updateType && this.updateType == 'updateCustomer') {
      value.userType = 'customer'
      value.id = this.fromParent.data.id
      this.customer.createCutomer(value).subscribe(res => {
        if (res.success) {
          this.close('update')
        }
        this.spinner.hide()
        this.cdr.markForCheck()
      }, (error) => {
        this.spinner.hide()
        this.toast.toastNotification1(error.error.message, 'Resolve Errors')
      });
    }

    else if (this.userType && this.userType == 'Contractor') {
      value.userType = 'contractor'
      this.contracotr.createContractor(value).subscribe(res => {
        if (res.success) {
          this.close('create')
        }
        else if (res[0]) {
          this.toast.toastNotification1(res[0], 'Resolve Errors')
        }
        this.spinner.hide()
        this.cdr.markForCheck()
      }, (error) => {
        this.toast.toastNotification1(error.error.message, 'Resolve Errors')
        this.spinner.hide()
      });
    }

    else {
      value.userType = 'customer'
      this.customer.createCutomer(value).subscribe(res => {
        if (res.success) {
          this.close('create')
          this.cdr.markForCheck()
        }
        else if (res[0]) {
          this.toast.toastNotification1(res[0], 'Resolve Errors')
        }
        this.spinner.hide()
      }, (error) => {
        this.toast.toastNotification1(error.error.message, 'Resolve Errors')
        this.spinner.hide()
      });
    }
  }

  /*AutoComplete Addres input */
  formattedAddress = ''
  options = {
    componentRestrictions: {
      country: ['AU']
    }
  }
  map: any;
  public handleAddressChange(address: any) {
    if (address) {
      this.formattedAddress = address.formatted_address;
      this.addCustomr.get('address').setValue(this.formattedAddress);

      address.address_components.forEach(component => {
        if (component.types.includes('administrative_area_level_2')) {
          this.addCustomr.get('city').setValue(component.long_name);
        }
        if (component.types.includes('administrative_area_level_1')) {
          this.addCustomr.get('state').setValue(component.long_name);
        }
        if (component.types.includes('postal_code')) {
          this.addCustomr.get('postal_code').setValue(component.long_name);
        }
      });

    } 
    else {
      this.addCustomr.get('address').setValue('');
      this.addCustomr.get('city').setValue('');
      this.addCustomr.get('postal_code').setValue('');
      this.addCustomr.get('state').setValue('');
    }
  }
  /*End AutoComplete Addres input */

  close(data?) {
    // this.addCustomr.reset();
    this.ngbActiveModal.dismiss(data);
  }

  getTabType(selected, el: HTMLElement) {
    this.tabType = selected;
    el.scrollIntoView();
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

  passwordComplexityValidator(): ValidatorFn {
    return (control: AbstractControl): ValidationErrors | null => {
      const value = control.value;
      if (!value) {
        return null;
      }
      const hasUpperCase = /[A-Z]+/.test(value);
      const hasSpecialChar = /[\W_]+/.test(value);
      const hasNumeric = /[0-9]+/.test(value);
      const isValid = hasUpperCase && hasSpecialChar && hasNumeric;
      return !isValid ? { passwordComplexity: true } : null;
    };
  }

}
