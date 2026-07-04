import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { AccountStatusComponent } from './account-status.component';
import { MaterialModule } from 'app/shared/material.module';



@NgModule({
  declarations: [AccountStatusComponent],
  imports: [
    CommonModule, MaterialModule
  ]
})
export class AccountStatusModule { }
