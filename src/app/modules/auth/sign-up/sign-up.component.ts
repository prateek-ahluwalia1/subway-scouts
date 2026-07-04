import { ServiceService } from 'app/services/service.service';
import { Component, ElementRef, OnInit, ViewChild, ViewEncapsulation } from '@angular/core';
import { UntypedFormBuilder, UntypedFormGroup, NgForm, Validators } from '@angular/forms';
import { Router } from '@angular/router';
import { fuseAnimations } from '@fuse/animations';
import { FuseAlertType } from '@fuse/components/alert';
import { AuthService } from 'app/core/auth/auth.service';
import { OtpVerificationComponent } from 'app/modules/admin/models/otp-verification/otp-verification.component';
import { ModalDismissReasons, NgbModal } from '@ng-bootstrap/ng-bootstrap';
import { finalize } from 'rxjs';

@Component({
  selector: 'auth-sign-up',
  templateUrl: './sign-up.component.html',
  encapsulation: ViewEncapsulation.None,
  animations: fuseAnimations
})

export class AuthSignUpComponent implements OnInit {

  @ViewChild('signUpNgForm') signUpNgForm: NgForm;

  alert: { type: FuseAlertType; message: string } = {
    type: 'success',
    message: ''
  };
  @ViewChild('UploadFileInput') uploadFileInput: ElementRef;
  myfilename = 'Select File';

  signUpForm: UntypedFormGroup;
  showAlert: boolean = false;
  loading: boolean = false; // Flag variable
  selectedFile: File = null; // Variable to store file
  selectedSate: 'Victoria'
  roles: any = []
  states: string[] = ['Victoria', 'New South Wales', 'Queensland', 'Tasmania', 'Western Australia', 'South Australia', 'ACT']

  constructor(
    private _authService: AuthService,
    private _formBuilder: UntypedFormBuilder,
    private _router: Router,
    private server: ServiceService,
    private modalService: NgbModal,
  ) { }

  ngOnInit(): void {
    this.signUpForm = this._formBuilder.group({
      first_name: ['', Validators.required],
      last_name: ['', Validators.required],
      email: ['', [Validators.required, Validators.email]],
      password: ['', Validators.required],
      phone: ['', Validators.required],
      state: ['', Validators.required],
      //image: ['',Validators.required],
      //agreements: ['', Validators.requiredTrue]
    });
  }

  onFileChanged(event) {
    this.selectedFile = event.target.files[0];
    console.log("file ", this.selectedFile);
    const reader = new FileReader();
    reader.onload = (e: any) => {
      const image = new Image();
      image.src = e.target.result;
      image.onload = rs => {
        const imgBase64Path = e.target.result;
        console.log('base64 ', imgBase64Path);
      };
      // this.signUpForm.set[]
    };
    reader.readAsDataURL(event.target.files[0]);
  }

  signUp(): void {
    if (this.signUpForm.invalid) {
      // this.otpVerification()
      return;
    }

    this.signUpForm.disable();

    this.showAlert = false;

    this.server.admin_signUp(this.signUpForm.value).pipe(
      finalize(() => {
        this.showAlert = true;
      })).subscribe((response) => {
        if (response.success) {
          this.signUpForm.enable();
          this.signUpNgForm.resetForm();
          this._router.navigateByUrl('/confirmation-required');
        }
        else {
          this.alert = {
            type: 'error',
            message: 'Email does not found! Are you sure you are already a member?'
          };
        }
        this.showAlert = true;
      }, 
      error => {
        this.signUpForm.enable();
        this.alert = {
          type: 'error',
          message: 'Something went wrong, please try again.'
        };
        if (error.error.errors.email) {
          this.alert = {
            type: 'error',
            message: error.error.errors.email[0]
          };
        }
      }
      )
    // .subscribe(
    //     (response) => {
    //         console.log("res " ,response);
    //          // Navigate to the confirmation required page
    //          this._router.navigateByUrl('/confirmation-required');


    //         // Re-enable the form
    //         this.signUpForm.enable();

    //         // Reset the form
    //         this.signUpNgForm.resetForm();

    //         // Set the alert
    //         this.alert = {
    //             type   : 'error',
    //             message: 'Something went wrong, please try again.'
    //         };

    //         // Show the alert
    //         this.showAlert = true;
    //     }
    // ),(error=>{
    //   console.log(error);
    // })
  }

  otpVerification() {
    const modalRef = this.modalService.open(OtpVerificationComponent, {
      scrollable: true, windowClass: "otp_page"
      , size: 'lg'
    })
    modalRef.componentInstance.fromParent = '';
    modalRef.result.then((result) => {
      console.log("Modal Result", `Closed with: ${result}`)
    }, (reason) => {
      console.log("Modal Closed Reason -> ", `Dismissed ${this.getDismissReason(reason)}`)

    });
  }
  getDismissReason(reason: any): string {
    if (reason === ModalDismissReasons.ESC) {
      return 'by pressing ESC';
    } else if (reason === ModalDismissReasons.BACKDROP_CLICK) {
      return 'by clicking on a backdrop';
    } else if (reason === 'updated') {
      console.log(reason);
    }
  }

}
