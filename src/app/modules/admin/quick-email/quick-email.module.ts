import { NgModule } from '@angular/core';
import { Route, RouterModule } from '@angular/router';
import { QuickEmailComponent } from './quick-email.component';
import { AdminModule } from '../admin.module';
import { SelectMultipleGuardsModule } from '../component/select-multiple-guards/select-multiple-guards.module';


const exampleRoutes: Route[] = [
  {
    path: '',
    component: QuickEmailComponent
  }]
@NgModule({
  declarations: [QuickEmailComponent],
  imports: [
    RouterModule.forChild(exampleRoutes), AdminModule, SelectMultipleGuardsModule
  ]
})
export class QuickEmailModule { }
