import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { TemplateComponent } from './template.component';
import { Route, RouterModule } from '@angular/router';
import { ReactiveFormsModule } from '@angular/forms';
import { MaterialModule } from 'app/shared/material.module';


const exampleRoutes: Route[]=[
  {
    path:'',
    component: TemplateComponent
  }]

@NgModule({
  declarations: [TemplateComponent],
  imports: [
    CommonModule,RouterModule.forChild(exampleRoutes),MaterialModule
  ]
})
export class TemplateModule { }
