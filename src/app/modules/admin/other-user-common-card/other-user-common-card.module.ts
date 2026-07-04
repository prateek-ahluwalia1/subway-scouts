import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { OtherUserCommonCardComponent } from './other-user-common-card.component';
import { Route, RouterModule } from '@angular/router';
import { AdminModule } from '../admin.module';

const exampleRoutes: Route[]=[
  {
    path:'',
    component: OtherUserCommonCardComponent,
  }
]

@NgModule({
  declarations: [OtherUserCommonCardComponent],
  imports: [
    CommonModule,AdminModule, RouterModule.forChild(exampleRoutes)
  ]
})
export class OtherUserCommonCardModule { }
