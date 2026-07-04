import { NgModule } from '@angular/core';
import { UsersComponent } from './users.component';
import { Route, RouterModule } from '@angular/router';
import { AdminModule } from '../admin.module';
const exampleRoutes: Route[] = [
  {
      path: '',
      component: UsersComponent
  },
  
];

@NgModule({
  declarations: [
    UsersComponent
  ],
  imports: [
    RouterModule.forChild(exampleRoutes),AdminModule
  ],
  exports:[UsersComponent,RouterModule]
})
export class UsersModule { }
