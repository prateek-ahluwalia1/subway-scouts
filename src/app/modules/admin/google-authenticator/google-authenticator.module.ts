import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterModule } from '@angular/router';
import { GoogleAuthenticatorComponent } from './google-authenticator.component';
import { HttpClientModule } from '@angular/common/http';

const exampleRoutes = [
  {
    path: '',
    component: GoogleAuthenticatorComponent
  }
];

@NgModule({
  declarations: [GoogleAuthenticatorComponent],
  imports: [CommonModule, RouterModule.forChild(exampleRoutes),HttpClientModule], // Add QRCodeModule to imports
})
export class GoogleAuthenticatorModule {}
