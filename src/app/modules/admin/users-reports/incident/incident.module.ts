import { NgModule } from '@angular/core';
import { IncidentComponent } from './incident.component';
import { RouterModule, Routes } from '@angular/router';
import { AdminModule } from '../../admin.module';

const routes: Routes = [
  {
    path: '',
    component: IncidentComponent,
  }
];

@NgModule({
  declarations: [IncidentComponent],
  imports: [RouterModule.forChild(routes),AdminModule]
})
export class IncidentModule { }
