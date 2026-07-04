import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { EmailsComponent } from './emails.component';
import { Route, RouterModule } from '@angular/router';

const exampleRoutes: Route[]=[
  {
    path:'',
    component: EmailsComponent
  }
]

@NgModule({
  declarations: [
    EmailsComponent
  ],
  imports: [
    CommonModule, RouterModule.forChild(exampleRoutes)
  ]
})
export class EmailsModule { }
