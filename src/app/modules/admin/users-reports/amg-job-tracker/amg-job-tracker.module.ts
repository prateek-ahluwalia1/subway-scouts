import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterModule, Routes } from '@angular/router';
import { AmgJobTrackerComponent } from './amg-job-tracker.component';
import { AdminModule } from '../../admin.module';

const routes: Routes = [
  {
    path: '',
    component: AmgJobTrackerComponent,
  }
];

@NgModule({
  declarations: [AmgJobTrackerComponent],
  imports: [
    CommonModule, RouterModule.forChild(routes), AdminModule
  ]
})
export class AmgJobTrackerModule { }
