import { Routes } from '@angular/router';
import { AddPackegeComponent } from './add-packege/add-packege.component';
import { AddBusinessComponent } from './add-business/add-business.component';
import { BusinessListComponent } from './business-list/business-list.component';
import { CompanyBusinessComponent } from './company-business.component';
import { PackegeDetailComponent } from './packege-detail/packege-detail.component';

export const businessRoutes: Routes = [
  {
    path: '',
    component: CompanyBusinessComponent,
    children: [
      {
        path: '',
        component: BusinessListComponent,
      },
      {
        path: 'add-packege',
        component: AddPackegeComponent,
      },
      {
        path: 'update-packege/:id',
        component: AddPackegeComponent,
      },
      {
        path: 'add-business',
        component: AddBusinessComponent,
      },
      {
        path: 'update-business/:id',
        component: AddBusinessComponent,
      },
      {
        path: 'packege-detail/:id',
        component: PackegeDetailComponent,
      }
    ]
  }
];

