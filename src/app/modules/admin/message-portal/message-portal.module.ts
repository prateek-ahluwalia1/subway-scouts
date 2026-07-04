import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { Route, RouterModule } from '@angular/router';
import { MessagePortalComponent } from './message-portal.component';


const exampleRoutes: Route[]=[
  {
    path:'',
    component:MessagePortalComponent
  }
]
@NgModule({
  declarations: [MessagePortalComponent],
  imports: [
    CommonModule,RouterModule.forChild(exampleRoutes)
  ]
})
export class MessagePortalModule { }
