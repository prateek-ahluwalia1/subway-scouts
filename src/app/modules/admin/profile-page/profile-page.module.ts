import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { Route, RouterModule } from '@angular/router';
import { ProfilePageComponent } from './profile-page.component';
import { AdminModule } from '../admin.module';

const exampleRoutes: Route[] = [
  {
      path     : '',
      component: ProfilePageComponent
  }
];



@NgModule({
  declarations: [ProfilePageComponent],
  imports: [
    CommonModule, 
    RouterModule.forChild(exampleRoutes),
    AdminModule
  ]
})
export class ProfilePageModule { }
