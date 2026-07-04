import { NgModule } from '@angular/core';
import { Route, RouterModule } from '@angular/router';
import { QuicksmsComponent } from './quicksms.component';
import { AdminModule } from '../admin.module';
import { SelectMultipleGuardsModule } from '../component/select-multiple-guards/select-multiple-guards.module';

const exampleRoutes: Route[] = [
  {
    path: '',
    component: QuicksmsComponent
  }]
@NgModule({
  declarations: [QuicksmsComponent],
  imports: [
    RouterModule.forChild(exampleRoutes), AdminModule, SelectMultipleGuardsModule
  ]
})
export class QuicksmsModule { }
