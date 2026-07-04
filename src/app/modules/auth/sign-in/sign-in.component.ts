import { AccountStatusComponent } from './../../admin/account-status/account-status.component';
import { Component, OnInit, TemplateRef, ViewChild, ViewEncapsulation } from '@angular/core';
import { UntypedFormBuilder, UntypedFormGroup, NgForm, Validators } from '@angular/forms';
import { Router } from '@angular/router';
import { fuseAnimations } from '@fuse/animations';
import { FuseAlertType } from '@fuse/components/alert';
import { AuthService } from 'app/core/auth/auth.service';
import { MatDialog, MatDialogRef } from '@angular/material/dialog';
import { GlobalVariable } from 'app/shared/global';
import { PortalSettingService } from 'app/services/portal-setting.service';
import { NgxSpinnerService } from 'ngx-spinner';

@Component({
    selector: 'auth-sign-in',
    templateUrl: './sign-in.component.html',
    encapsulation: ViewEncapsulation.None,
    animations: fuseAnimations
})

export class AuthSignInComponent implements OnInit {
    checked = false;
    indeterminate = false;
    labelPosition: 'before' | 'after' = 'after';
    disabled = false;
    @ViewChild('signInNgForm') signInNgForm: NgForm;
    @ViewChild('businesses') businesses: TemplateRef<any>;
    dialogRef: MatDialogRef<any>;

    alert: { type: FuseAlertType; message: string } = {
        type: 'success',
        message: ''
    };
    signInForm: UntypedFormGroup;
    showAlert: boolean = false;
    email
    otp
    password
    selectedOption
    business: any = []
    showOTP: boolean = false

    constructor(
        private _authService: AuthService,
        private _formBuilder: UntypedFormBuilder,
        private _router: Router,
        public dialog: MatDialog,
        public global: GlobalVariable,
        public portalSetting: PortalSettingService,
        private spinnerService: NgxSpinnerService,
    ) {}

    ngOnInit(): void {
        this.signInForm = this._formBuilder.group({
            email: ['', [Validators.required, Validators.email]],
            password: ['', Validators.required],
        });
    }

    okey(res) {
        this.spinnerService.show();
        if(res.success){
            this.handleSignInSuccess(res)
        }
        else {
            this.handleSignInError(res)
        }
        // this.signInForm.value.request_db = this.selectedOption;
        // if (this.showOTP && !this.otp) {
        //     this.alert = { type: 'error', message: 'Please enter your otp' };
        //     this.spinnerService.hide()
        //     return
        // }
        // this.signInForm.value.otp = this.otp;

        // this._authService.signInWithBusiness(this.signInForm.value).subscribe(
        //     (res) => this.handleSignInSuccess(res),
        //     (error) => this.handleSignInError(error)
        // );
    }
    private handleSignInSuccess(res: any) {
        if (res.success && res.status === 'active') {
            localStorage.setItem('acknowledged', JSON.stringify(true));
            this.global.admin = JSON.parse(localStorage.getItem('admin'))
            if (this.global.admin && this.global.admin.apiKeys && this.global.admin.apiKeys.logo) {
                localStorage.setItem('logo', this.global.admin.apiKeys.logo)
            } else {
                localStorage.setItem('logo', 'assets/images/logo/scouts.png')
            }
            this.setPortalColors();
            this.handleActiveUser(res);
        } else if (res.status === 'inactive') {
            this.handleInactiveUser();
        } else {
            this.handleWrongCredentials(res);
        }
    }
    private handleActiveUser(res: any) {
        this.spinnerService.hide();
        let redirectURL: string;
        if (res && res.userType == 'saleperson') {
            redirectURL = '/crm-dashboard';
        } else if (res.chat_type === 'super-admin' || res.chat_type === 'admin') {
            redirectURL = '/dashboard';
        }
        else {
            redirectURL = '/document-setting';
        }
        this._router.navigateByUrl(redirectURL);
    }

    private handleInactiveUser() {
        this.spinnerService.hide();
        this._router.navigateByUrl('sign-in');
        this.signInForm.enable();
        this.openDialog('Inactive user');
    }

    private handleWrongCredentials(res) {
        this.spinnerService.hide();
        this.dialogRef.close();
        this.showAlert = true;
        this.otp = ''
        this.selectedOption = ''
        this.alert = { type: 'error', message: res.error ? res.error : res.message };
        this.signInForm.enable();
    }

    private handleSignInError(error: any) {
        console.log(error);
        this.spinnerService.hide();
        this.signInForm.enable();
        if (error.error.error) {
            this.alert = {
                type: 'error',
                message: error.error.error
            };
        }
        else {
            this.alert = {
                type: 'error',
                message: 'Something went wrong please contact with support team'
            };
        }

        this.showAlert = true;
    }

    private setPortalColors() {
        this.portalSetting.portalSetting().subscribe(({ data, success }) => {
            if (success) {
                const colorSettings = JSON.parse(data.settings);
                this.global.portalSetting = colorSettings;
                for (const key in colorSettings) {
                    if (colorSettings.hasOwnProperty(key)) {
                        const colorVarName = `--${key}`;
                        const colorValue = colorSettings[key];
                        document.documentElement.style.setProperty(colorVarName, colorValue);
                    }
                }
                localStorage.setItem('color', JSON.stringify(colorSettings));
            }
        });
    }

    signIn(): void {
        if (this.signInForm.invalid) {
            return;
        }

        this.disableSignInForm();

        this._authService.signIn(this.signInForm.value).subscribe(
            (res) => {
                if (res.success) {
                    if (res.admin_id) {
                        this.okey(res);
                    } 
                    // else {
                    //     this.handleBusinessDialog(res.data);
                    // }
                } else if (!res.success) {
                    this.signInForm.enable();
                    this.alert = {
                        type: 'error',
                        message: res.error
                    };
                    this.showAlert = true;
                }
                else if (res.status === 'inactive') {
                    this.handleInactiveUsers(res.error);

                }
                else {
                    this.handleFailedSignIn();
                }
            },
            (error) => {
                console.log(error);
                this.handleSignInError(error)
                this.enableSignInForm();
                // this.handleFailedSignIn();
            }
        );
    }

    private disableSignInForm(): void {
        this.signInForm.disable();
        this.showAlert = false;
    }

    private enableSignInForm(): void {
        this.signInForm.enable();
    }

    private handleBusinessDialog(data: any): void {
        this.business = data;
        this.dialogRef = this.dialog.open(this.businesses, {
            height: 'auto',
            width: '400px',
            minHeight: '300px'
        });

        this.dialogRef.afterClosed().subscribe((result) => {
            if (!result) {
                this.enableSignInForm();
            }
        });
    }

    private handleInactiveUsers(error: string): void {
        this._router.navigateByUrl('sign-in');
        this.signInForm.enable();
        this.openDialog(error);
    }

    private handleFailedSignIn(): void {
        this.alert = {
            type: 'error',
            message: 'Wrong email or password'
        };
        this.enableSignInForm();
    }


    openDialog(message): void {

        const dialogRef = this.dialog.open(AccountStatusComponent, {
            width: '400px',
            data: { name: 'loginstatus', message: message }
        });

        dialogRef.afterClosed().subscribe(() => {
            console.log('The dialog was closed');

        });
    }


    onBusinessSelected() {
        let data = {
            email: this.signInForm?.value.email,
            database_name: this.selectedOption
        }
        // Check if a valid business is selected (not the default "Choose business" option)
        if (this.selectedOption && this.selectedOption !== 'Choose business') {
            this._authService.checkOTP(data).subscribe(({ success }) => {
                if (success) {
                    this.showAlert = false;
                    this.showOTP = true
                }
                else {
                    this.showAlert = false;
                    this.showOTP = false
                }
            }, (error => {
                // this.
                this.showAlert = true;
                this.alert = { type: 'error', message: 'Something went wrong. Please contact with support team' };
            }))

        }
    }
}
