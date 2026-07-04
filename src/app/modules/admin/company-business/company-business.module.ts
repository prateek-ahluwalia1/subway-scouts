import { NgModule } from '@angular/core';
import { businessRoutes } from './company-business-routing.module';
import { AddPackegeComponent } from './add-packege/add-packege.component';
import { AdminModule } from '../admin.module';
import { CompanyBusinessComponent } from './company-business.component';
import { AddBusinessComponent } from './add-business/add-business.component';
import { BusinessListComponent } from './business-list/business-list.component';
import { RouterModule } from '@angular/router';
import { PackegeDetailComponent } from './packege-detail/packege-detail.component';


@NgModule({
  declarations: [
    AddPackegeComponent, CompanyBusinessComponent, AddBusinessComponent, BusinessListComponent, PackegeDetailComponent
  ],
  imports: [
    RouterModule.forChild(businessRoutes), AdminModule
  ]
})
export class CompanyBusinessModule { }
