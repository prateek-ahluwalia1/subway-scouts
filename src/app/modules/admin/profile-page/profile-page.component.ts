import { Component, OnInit } from '@angular/core';
import { AbstractControl, FormBuilder, FormGroup, ValidationErrors, ValidatorFn, Validators } from '@angular/forms';
import { DomSanitizer, SafeHtml } from '@angular/platform-browser';
import { ActivatedRoute } from '@angular/router';
import { AdminService } from 'app/services/admin.service';
import { ToastServiceService } from 'app/services/toast-service.service';
import { GlobalVariable } from 'app/shared/global';

@Component({
  selector: 'app-profile-page',
  templateUrl: './profile-page.component.html',
  styleUrls: ['./profile-page.component.scss']
})

export class ProfilePageComponent implements OnInit {

  is2FAEnabled: boolean = false;
  qrcode: SafeHtml;
  accountForm: FormGroup;
  submitted: boolean = false;
  id: any;
  admin_data: any;

  constructor(private route: ActivatedRoute, private adminservice: AdminService,
    public global: GlobalVariable, private sanitize: DomSanitizer, private toast: ToastServiceService , 
    private formBuilder: FormBuilder) { 

    this.accountForm = this.formBuilder.group({
      current_password: ['', Validators.required],
      new_password: ['', [Validators.required, Validators.maxLength(8)]],
      confirm_password: ['', [Validators.required]],
    }, { validators: this.passwordMatchValidator });
  }

  ngOnInit(): void {
    this.route.queryParams.subscribe(params => {
      this.id = params['id'];
    });

    if (this.id) {
      this.getProfile()
    } 
  }

  getProfile() {
    this.adminservice.getAdminData(this.id).subscribe(({ success, data }) => {
      if (success) {
        this.admin_data = data;
        // this.is2FAEnabled = data?.is_2fa_enable == 1 ? true : false
        this.qrcode = this.sanitize.bypassSecurityTrustHtml(data?.qr_image ?? 'No QR Code Available');
        // this.accountForm.setValue({
        //   abn: data?.abn,
        //   acn: data?.acn,
        //   bank_name: data?.bank_name,
        //   bsb: data?.bsb,
        //   account_no: data?.account_no
        // });
      }
    }, (error) => {
      console.log(error);
    });
  }

  onToggleChange(event: Event) {
    const isChecked = (event.target as HTMLInputElement).checked;
    const is2FAEnabled = isChecked ? 1 : 0;
    console.log(isChecked);
    let data = {
      type: this.global.admin?.admin_user_type,
      is_2fa_enable: is2FAEnabled,
      id: this.global.admin.admin_id
    }
    this.adminservice.enableOrDisable2FA(data).subscribe(res => {
      if (res.success) {
        this.getProfile()
        this.toast.toastNotification(res.message, '2FA')
      }
      else {
        this.toast.toastNotification(res.message, '2FA')
      }
    }, error => {
      this.toast.toastNotification1(this.global.apiError, 'Error')
    })
  }
  
  updateAccountDetails() {
    const updatedData = this.accountForm.value;
    this.adminservice.updateAdmin(updatedData).subscribe(res => {
      if (res.success) {
        // Handle success, e.g., show a success message
        this.toast.toastNotification(res.message, 'Success');
        // Optionally, update the profile data after successful update
        this.getProfile();
      } else {
        // Handle error, e.g., show an error message
        this.toast.toastNotification(res.message, 'Error');
      }
    }, error => {
      // Handle API error
      this.toast.toastNotification1(this.global.apiError, 'Error');
    });
  }

  change_password() {
    this.submitted = true;
    if (this.accountForm.valid) {
      const formData = { ...this.accountForm.value, admin_id: this.global.admin.admin_id };
      this.adminservice.changepassword(formData).subscribe({
        next: (response) => {
          console.log(response);
          if (response.success) {
            this.toast.toastNotification(response.message, 'Success');
          } else {
            this.toast.toastNotification1(response.message, 'Error');
          }
        },
        error: (errorResponse) => {
          const errorMessage = (errorResponse.error && errorResponse.error.message) || 'An error occurred';
          this.toast.toastNotification1(errorMessage, 'Error');
        }
      });
    }
  }
  
  passwordMatchValidator: ValidatorFn = (control: AbstractControl): { [key: string]: any } | null => {
    const password = control.get('new_password')?.value;
    const confirmPassword = control.get('confirm_password')?.value;
    return password !== confirmPassword ? { passwordMismatch: true } : null;
  }

}
