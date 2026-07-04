import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { authEmailVerifyRoutes } from 'app/modules/auth/email-verify/email-verify-routing'
import { RouterModule } from '@angular/router';



@NgModule({
  declarations: [],
  imports: [
    CommonModule,RouterModule.forChild(authEmailVerifyRoutes)
  ]
})
export class EmailVerifyModule { }
