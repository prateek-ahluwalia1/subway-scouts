import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { CommunicationComponent } from './communication.component';
import { Route, RouterModule } from '@angular/router';
import { AdminModule } from '../admin.module';
import { AnnouncementsComponent } from '../announcements/announcements.component';

const exampleRoutes: Route[] =[
  {
    path: '',
    component: CommunicationComponent
  }
]

@NgModule({
  declarations: [
    CommunicationComponent,AnnouncementsComponent
  ],
  imports: [
    CommonModule,RouterModule.forChild(exampleRoutes),AdminModule
  ]
})
export class CommunicationModule { }
