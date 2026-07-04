import { Component, OnInit } from '@angular/core';
import { AbstractControl, FormControl, FormGroup, ValidationErrors, ValidatorFn, Validators } from '@angular/forms';
import { NgbActiveModal } from '@ng-bootstrap/ng-bootstrap';
import { StaffService } from 'app/services/staff.service';
import { ToastServiceService } from 'app/services/toast-service.service';
import { TrackAdminActivityService } from 'app/services/track-admin-activity.service';
import { GlobalVariable } from 'app/shared/global';

@Component({
  selector: 'app-on-boarding-staff',
  templateUrl: './on-boarding-staff.component.html',
  styleUrls: ['./on-boarding-staff.component.scss']
})
export class OnBoardingStaffComponent implements OnInit {
  indeterminate = false;
  disabled = false;
  error
  showerror: boolean = false
  addOnBoardingStaff: FormGroup;
  constructor(public ngbActiveModal: NgbActiveModal, private staffService: StaffService,
    public toast: ToastServiceService, private global: GlobalVariable, private trackAdmin: TrackAdminActivityService) { }

  ngOnInit(): void {
    this.addOnBoardingStaff = new FormGroup({
      first_name: new FormControl('', [Validators.required, this.alphabetValidator]),
      middle_name: new FormControl(''),
      last_name: new FormControl('', [Validators.required, this.alphabetValidator]),
      email: new FormControl('', [Validators.required, Validators.email]),
      phone: new FormControl(''),
      guard_type: new FormControl(''),
      staff_type: new FormControl('', Validators.required),
      state: new FormControl('', Validators.required),
      password: new FormControl('', [Validators.required,Validators.minLength(8), this.passwordComplexityValidator()]),
      c_password: new FormControl('', [Validators.required]),
    }, {
      validators: this.passwordMatchValidator
    });
  }

  alphabetValidator(control: AbstractControl): ValidationErrors | null {
    const value = control.value;
    if (value && !/^[A-Za-z][A-Za-z0-9\s]*$/.test(value)) {

      return { alphabet: true };
    }
    return null;
  }

  // validatePhone(control: FormControl): ValidationErrors | null {
  //   const pattern = /^61(?:2|3|4|7|8)\d{8}$/;
  //   if (control.value && !pattern.test(control.value)) {
  //     return { invalidPhone: true };
  //   }
  //   return null;
  // }


  onSubmit() {
    Object.values(this.addOnBoardingStaff.controls).forEach(control => control.markAsTouched());
    if (this.addOnBoardingStaff.invalid) {
      this.toast.toastNotification1('Please check error on field and fill this field', 'Invalid Form!')
      return
    }
    this.disableForm()
    this.addOnBoardingStaff.value.profile_completion = '30'
    this.addOnBoardingStaff.value.admin_id = this.global.admin.admin_id
    console.log(this.addOnBoardingStaff.value);
    this.staffService.addQickStaff(this.addOnBoardingStaff.value).subscribe(res => {
      if (res.success) {
        let status = 'Staff Operation'
        this.toast.toastNotification(res.message, status);
        this.close('QuickStaff')
        this.trackAdmin.storeActivity('Staff', `Added a onboarding staff with name: ${this.addOnBoardingStaff.value.first_name + ' ' + this.addOnBoardingStaff.value.middle_name + ' ' + this.addOnBoardingStaff.value.last_name}`, localStorage.getItem('routerId')).subscribe(({ success, id }) => {
          this.addOnBoardingStaff.reset()
        })
      }
      else {
        this.enableForm()
      }
    }, (error) => {
      this.enableForm()
      this.showerror = true
      this.error = 'Email already exist'
      console.log(error);
    });
  }

  close(data?) {
    this.ngbActiveModal.dismiss(data);
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
        return null; // Don't validate empty value
      }
      const hasUpperCase = /[A-Z]+/.test(value);
      const hasSpecialChar = /[\W_]+/.test(value);
      const hasNumeric = /[0-9]+/.test(value);
      const isValid = hasUpperCase && hasSpecialChar && hasNumeric;
      return !isValid ? { passwordComplexity: true } : null;
    };
  }

  private disableForm(): void {
    this.addOnBoardingStaff.disable();
    // this.showAlert = false;
  }

  private enableForm(): void {
    this.addOnBoardingStaff.enable();
  }
}
