import { NgModule } from '@angular/core';
import { TaskComponent } from './task.component';
import { RouterModule, Routes } from '@angular/router';
import { AdminModule } from '../../admin.module';
import { FilterPipe } from './filter.pipe';

const routes: Routes = [
  {
    path: '',
    component: TaskComponent,
  }
];

@NgModule({
  declarations: [TaskComponent,FilterPipe],
  imports: [
    RouterModule.forChild(routes), AdminModule, 
  ]
})
export class TaskModule { }
