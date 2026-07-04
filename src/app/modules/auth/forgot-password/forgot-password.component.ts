import { Component, OnInit, TemplateRef, ViewChild, ViewEncapsulation } from '@angular/core';
import { UntypedFormBuilder, UntypedFormGroup, NgForm, Validators } from '@angular/forms';
import { finalize } from 'rxjs';
import { fuseAnimations } from '@fuse/animations';
import { FuseAlertType } from '@fuse/components/alert';
import { AuthService } from 'app/core/auth/auth.service';
import { NgxSpinnerService } from 'ngx-spinner';
import { MatDialog, MatDialogRef } from '@angular/material/dialog';

@Component({
    selector: 'auth-forgot-password',
    templateUrl: './forgot-password.component.html',
    encapsulation: ViewEncapsulation.None,
    animations: fuseAnimations
})

export class AuthForgotPasswordComponent implements OnInit {
    @ViewChild('forgotPasswordNgForm') forgotPasswordNgForm: NgForm;
    @ViewChild('businesses') businesses: TemplateRef<any>;
    selectedOption
    business: any = []
    alert: { type: FuseAlertType; message: string } = {
        type: 'success',
        message: ''
    };
    forgotPasswordForm: UntypedFormGroup;
    showAlert: boolean = false;
    dialogRef: MatDialogRef<any>;
    constructor(
        private _authService: AuthService,
        private _formBuilder: UntypedFormBuilder,
        private spinnerService: NgxSpinnerService,
        public dialog: MatDialog,
    ) {}

    ngOnInit(): void {
        this.forgotPasswordForm = this._formBuilder.group({
            email: ['', [Validators.required, Validators.email]]
        });
    }

    private handleBusinessDialog(data: any): void {
        this.business = data;
        this.dialogRef = this.dialog.open(this.businesses, {
            height: '300px',
            width: '400px'
        });
        this.dialogRef.afterClosed().subscribe((result) => {
            if (!result) {
                this.forgotPasswordForm.enable();
            }
        });
    }

    sendResetLink(): void {
        if (this.forgotPasswordForm.invalid) {
            return;
        }

        this.forgotPasswordForm.disable();

        this._authService.forgotPassword(this.forgotPasswordForm.value).subscribe(
            (response) => {
                if (response.success && !response.hide) {
                    // this.handleBusinessDialog(response.data);
                }
                else if (response.success && response.hide) {
                    this.showAlert = true
                    this.alert = {
                        type: 'success',
                        message: response.message
                    };
                    this.forgotPasswordForm.enable();
                }
                else {
                    this.handleWrongCredentials(response)
                }
            },
        );
    }

    okey() {
        this.forgotPasswordForm.value.request_db = this.selectedOption;
        this._authService.forgotPassword(this.forgotPasswordForm.value)
        .pipe(
            finalize(() => {
                this.forgotPasswordForm.enable();

                this.forgotPasswordNgForm.resetForm();

                this.showAlert = true;
            })
        )
        .subscribe(
            (res) => {
                this.dialogRef.close();
                if (res.success) {
                    this.alert = {
                        type: 'success',
                        message: 'Password reset sent! You\'ll receive an email if you are registered on our system.'
                    };
                }
                else {
                    this.alert = {
                        type: 'error',
                        message: 'Email does not found! Are you sure you are already a member?'
                    };
                }
            }
        );
    }

    onSelectChange(selectedValue: string) {
        this.selectedOption = selectedValue;
    }
    
    private handleWrongCredentials(res) {
        this.spinnerService.hide();
        this.showAlert = true;
        this.selectedOption = ''
        this.alert = { type: 'error', message: res.error ? res.error : res.message };
        this.forgotPasswordForm.enable();
    }
}
