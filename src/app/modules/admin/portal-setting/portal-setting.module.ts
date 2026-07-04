import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';

import { routes } from './portal-setting-routing.module';
import { CompanyProfileComponent } from './company-profile/company-profile.component';
import { ColorSettingComponent } from './color-setting/color-setting.component';
import { RouterModule } from '@angular/router';
import { PortalSettingComponent } from './portal-setting.component';
import { ChildComponent } from './child/child.component';
import { ApiSettingComponent } from './api-setting/api-setting.component';
import { RoleAndPermissionComponent } from './role-and-permission/role-and-permission.component';
import { PhSettingComponent } from './ph-setting/ph-setting.component';
import { MaterialModule } from 'app/shared/material.module';
import { FullCalendarModule } from '@fullcalendar/angular';
import { DemoComponent } from './ph-setting/demo/demo.component';
import { ComplianceComponent } from './compliance/compliance.component';



@NgModule({
  declarations: [
    PortalSettingComponent,
    CompanyProfileComponent,
    ColorSettingComponent,
    ChildComponent,
    ApiSettingComponent,
    RoleAndPermissionComponent,
    PhSettingComponent,
    DemoComponent,
    ComplianceComponent
  ],
  imports: [
    CommonModule,
    RouterModule.forChild(routes), MaterialModule, FullCalendarModule
  ]
})
export class PortalSettingModule { }
