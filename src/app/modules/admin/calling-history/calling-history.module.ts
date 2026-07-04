import { NgModule } from '@angular/core';
import { CallingHistoryComponent } from './calling-history.component';
import { Route, RouterModule } from '@angular/router';
import { AdminModule } from '../admin.module';
const exampleRoutes: Route[] = [
  {
    path: '',
    component: CallingHistoryComponent
  }
]
@NgModule({
  declarations: [CallingHistoryComponent],
  imports: [
    RouterModule.forChild(exampleRoutes), AdminModule
  ]
})
export class CallingHistoryModule { }
