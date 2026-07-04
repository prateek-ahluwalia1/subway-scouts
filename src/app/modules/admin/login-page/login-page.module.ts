import { NgModule } from '@angular/core';
import { LoginPageComponent } from './login-page.component';
import { Route, RouterModule } from '@angular/router';
import { AdminModule } from '../admin.module';

const exampleRoutes: Route[]=[
  {
    path:'',
    component:LoginPageComponent
  }
]

@NgModule({
  declarations: [
    LoginPageComponent
  ],
  imports: [
    RouterModule.forChild(exampleRoutes),AdminModule
  ]
})
export class LoginPageModule { }
