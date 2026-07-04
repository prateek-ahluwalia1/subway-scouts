import { NgModule } from '@angular/core';
import { AnnouncementsComponent } from './announcements.component';
import { Route, RouterModule } from '@angular/router';
import { AdminModule } from '../admin.module';
const exampleRoutes: Route[] =[
  {
    path: '',
    component: AnnouncementsComponent
  }
]
@NgModule({
  declarations: [
    
  ],
  imports: [
    RouterModule.forChild(exampleRoutes),
    AdminModule
  ]
})
export class AnnouncementsModule { }
