import { CUSTOM_ELEMENTS_SCHEMA, NgModule, NO_ERRORS_SCHEMA } from '@angular/core';
import { Route, RouterModule } from '@angular/router';
import { StaffMessagesComponent } from './staff-messages.component';
import { MaterialModule } from 'app/shared/material.module';

const exampleRoutes: Route[] = [
  {
      path     : '',
      component: StaffMessagesComponent
  }
];

@NgModule({
  declarations: [StaffMessagesComponent],
  imports: [
    RouterModule.forChild(exampleRoutes),MaterialModule  ]
  ,schemas:[NO_ERRORS_SCHEMA,CUSTOM_ELEMENTS_SCHEMA]
})
export class StaffMessagesModule { }
