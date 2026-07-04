import { Component, OnInit } from '@angular/core';
import { GlobalVariable } from 'app/shared/global';
import { StaffService } from 'app/services/staff.service';
import { DomSanitizer, SafeHtml } from '@angular/platform-browser';
import { ToastServiceService } from 'app/services/toast-service.service';

@Component({
  selector: 'app-google-authenticator',
  templateUrl: './google-authenticator.component.html',
  styleUrls: ['./google-authenticator.component.scss']
})
export class GoogleAuthenticatorComponent implements OnInit {

  qrcode: SafeHtml;

  constructor(private service: StaffService, private sanitizer: DomSanitizer, private toast: ToastServiceService,
    public global: GlobalVariable) { }

  ngOnInit(): void {
    // Make an API call here
    this.makeApiCall();
  }

  makeApiCall() {
    this.service.googleAuthenticator().subscribe(response => {
      this.qrcode = this.sanitizer.bypassSecurityTrustHtml(response.QR_Image);
    }, (error) => {
      this.toast.toastNotification1('Something went wrong. Please contact with support team.', 'Request Incomplete!')
    });
  }
}
