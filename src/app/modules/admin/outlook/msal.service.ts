import { Injectable } from '@angular/core';
import { MsalService, MsalBroadcastService } from '@azure/msal-angular';
import { InteractionType } from '@azure/msal-browser';

@Injectable({
  providedIn: 'root',
})
export class MsalAuthService {
  constructor(
    private msalService: MsalService,
    private msalBroadcastService: MsalBroadcastService
  ) {
    // Set the instance of MSAL
    // this.msalService.instance = msalInstance;
  }

  login() {
    this.msalService.loginPopup().subscribe({
      next: (response) => {
        console.log('Login successful:', response);
      },
      error: (error) => {
        console.log('Login error:', error);
      },
    });
  }

  logout() {
    this.msalService.logout();
  }

  getAccount() {
    return this.msalService.instance.getActiveAccount();
  }

  // Add more methods for accessing Outlook data
}
