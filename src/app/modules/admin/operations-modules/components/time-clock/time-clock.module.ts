import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { TimeClockComponent } from './time-clock.component';
import { Route, RouterModule } from '@angular/router';
import { MaterialModule } from 'app/shared/material.module';
import { siteFilterPipe } from './site-filter.pipe';


const routes: Route[] = [
  {
    path: '',
    component: TimeClockComponent
  }
];

@NgModule({
  declarations: [TimeClockComponent, siteFilterPipe],
  imports: [
    CommonModule,
    RouterModule.forChild(routes), MaterialModule
  ]
})
export class TimeClockModule { }
