import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { Route, RouterModule } from '@angular/router';
import { AlarmDispatchSystemComponent } from './alarm-dispatch-system.component'
import { AdminModule } from 'app/modules/admin/admin.module';

const exampleRoutes: Route[] = [
  {
      path     : '',
      component: AlarmDispatchSystemComponent
  }
];

@NgModule({
  declarations: [AlarmDispatchSystemComponent],
  imports: [
    CommonModule, RouterModule.forChild(exampleRoutes), AdminModule
  ]
})
export class AlarmDispatchSystemModule { }
