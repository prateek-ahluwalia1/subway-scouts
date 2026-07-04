import { NgModule } from '@angular/core';
import { AdminModule } from '../../admin.module';
import { RouterModule, Routes } from '@angular/router';
import { SignInOutComponent } from './sign-in-out.component';

const routes: Routes = [
  {
    path: '',
    component: SignInOutComponent,
  }
];

@NgModule({
  declarations: [SignInOutComponent],
  imports: [RouterModule.forChild(routes),AdminModule]
})
export class SignInOutModule { }
