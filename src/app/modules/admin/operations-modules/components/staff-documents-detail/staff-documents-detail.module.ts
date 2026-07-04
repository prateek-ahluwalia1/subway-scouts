import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { StaffDocumentsDetailComponent } from './staff-documents-detail.component';
import { Route, RouterModule } from '@angular/router';
import { MaterialModule } from 'app/shared/material.module';

const exampleRoutes: Route[]=[
  {
    path:'',
    component:StaffDocumentsDetailComponent
  }
]

@NgModule({
  declarations: [
    StaffDocumentsDetailComponent
  ],
  imports: [
    CommonModule,
    RouterModule.forChild(exampleRoutes),MaterialModule

  ]
})

export class StaffDocumentsDetailModule { }
