import { NgModule } from '@angular/core';
import { Route, RouterModule } from '@angular/router';
import { ActivityLogComponent } from './activity-log.component';
import { Ng2SearchPipeModule } from 'ng2-search-filter';
import { MatDatepickerModule } from '@angular/material/datepicker';
import { SelectMultipleGuardsModule } from 'app/modules/admin/component/select-multiple-guards/select-multiple-guards.module';
import { MaterialModule } from 'app/shared/material.module';
const exampleRoutes: Route[] = [
  {
    path: '',
    component: ActivityLogComponent
  }
]


@NgModule({
  declarations: [
    ActivityLogComponent
  ],
  imports: [
    SelectMultipleGuardsModule,
    RouterModule.forChild(exampleRoutes),
    MatDatepickerModule,
    Ng2SearchPipeModule,
    MaterialModule
  ]
})
export class ActivityLogModule { }
