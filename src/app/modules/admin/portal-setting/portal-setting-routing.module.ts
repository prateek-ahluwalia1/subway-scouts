import { Routes } from '@angular/router';
import { PortalSettingComponent } from './portal-setting.component';
import { ColorSettingComponent } from './color-setting/color-setting.component';
import { CompanyProfileComponent } from './company-profile/company-profile.component';
import { ChildComponent } from './child/child.component';
import { ApiSettingComponent } from './api-setting/api-setting.component';
import { RoleAndPermissionComponent } from './role-and-permission/role-and-permission.component';
import { PhSettingComponent } from './ph-setting/ph-setting.component';
import { ComplianceComponent } from './compliance/compliance.component';

export const routes: Routes = [
  {
    path: '',
    component: PortalSettingComponent,
    children: [
      {
        path: '',
        component: ChildComponent
      },
      {
        path: 'color-setting',
        component: ColorSettingComponent
      },
      {
        path: 'company-profile',
        component: CompanyProfileComponent
      },
      {
        path: 'api-setting',
        component: ApiSettingComponent
      },
      {
        path: 'role-and-permissions',
        component: RoleAndPermissionComponent
      },
      {
        path: 'ph-setting',
        component: PhSettingComponent
      },
      {
        path: 'compliance',
        component: ComplianceComponent
      }
    ]
  }
];

