import { Component, OnInit, ViewChild, ViewEncapsulation } from '@angular/core';
import { UntypedFormBuilder, UntypedFormGroup, NgForm, Validators } from '@angular/forms';
import { finalize } from 'rxjs';
import { fuseAnimations } from '@fuse/animations';
import { FuseValidators } from '@fuse/validators';
import { FuseAlertType } from '@fuse/components/alert';
import { AuthService } from 'app/core/auth/auth.service';
import { ActivatedRoute, Router } from '@angular/router';

@Component({
    selector: 'auth-reset-password',
    templateUrl: './reset-password.component.html',
    encapsulation: ViewEncapsulation.None,
    animations: fuseAnimations
})

export class AuthResetPasswordComponent implements OnInit {

    @ViewChild('resetPasswordNgForm') resetPasswordNgForm: NgForm;
    alert: { type: FuseAlertType; message: string } = {
        type: 'success',
        message: ''
    };
    resetPasswordForm: UntypedFormGroup;
    showAlert: boolean = false;
    email
    token
    database

    constructor(
        private _authService: AuthService,
        private _formBuilder: UntypedFormBuilder,
        private route: ActivatedRoute,
        private _router: Router,
    ) {}

    ngOnInit(): void {

        this.route.queryParams.subscribe((params) => {
            this.email = params['email'];
            this.token = params['token'];
            this.database = params['database'];
        });

        this.resetPasswordForm = this._formBuilder.group({
            password: ['',[Validators.required, Validators.pattern(/^\S*$/)]],
            passwordConfirm: ['', Validators.required],
        }, 
        {
            validators: FuseValidators.mustMatch('password', 'passwordConfirm'),
        });
    }

    resetPassword(): void {
        if (this.resetPasswordForm.invalid) {
            return;
        }

        this.resetPasswordForm.disable();

        this.showAlert = false;

        let data = {
            password: this.resetPasswordForm.get('password').value,
            email: this.email,
            token: this.token,
            database: this.database

        }
        this._authService.resetPassword(data).pipe(
            finalize(() => {
                this.resetPasswordForm.enable();
                this.resetPasswordNgForm.resetForm();
                this.showAlert = true;
            })
        )
        .subscribe(
            (response) => {
                const redirectURL = '/sign-in'
                this.alert = {
                    type: 'success',
                    message: 'Your password has been reset.'
                };
                setTimeout(() => {
                    this._router.navigateByUrl(redirectURL);
                }, 2000);
            },
            (response) => {
                this.alert = {
                    type: 'error',
                     message: 'Something went wrong, please try again.'
                };
            }
        );
    }
}
