import { Ng2SearchPipeModule } from 'ng2-search-filter';
import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { GuardLicenseComponent } from './guard-license.component';
import { Route, RouterModule } from '@angular/router';
import { MatSlideToggleModule } from '@angular/material/slide-toggle';
import { MatChipsModule } from '@angular/material/chips';
import { MatIconModule } from '@angular/material/icon';
import { NgxPaginationModule } from 'ngx-pagination';
import { FormsModule } from '@angular/forms';

// import {
//   ConfirmBoxConfigModule,
//   DialogConfigModule,
//   NgxAwesomePopupModule,
//   ToastNotificationConfigModule,
// } from '@costlydeveloper/ngx-awesome-popup';
const exampleRoutes: Route[] =[
  {
    path: '',
    component: GuardLicenseComponent
  }
]
@NgModule({
  declarations: [
    GuardLicenseComponent
  ],
  imports: [
    CommonModule,
    RouterModule.forChild(exampleRoutes),
    MatSlideToggleModule,
    MatChipsModule,
    MatIconModule,
    NgxPaginationModule,
    FormsModule,
    Ng2SearchPipeModule
  
    
  ]
})
export class GuardLicenseModule { }
