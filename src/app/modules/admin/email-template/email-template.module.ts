import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { Route, RouterModule } from '@angular/router';
import { EmailTemplateComponent } from './email-template.component';
import {MatExpansionModule} from '@angular/material/expansion';
import { MatFormFieldModule } from '@angular/material/form-field';
import { ReactiveFormsModule } from '@angular/forms';
import { MatInputModule } from '@angular/material/input';
import {MatIconModule} from '@angular/material/icon';
import { FormsModule } from '@angular/forms';
import { EmailEditorModule } from 'angular-email-editor';
import { MatChipsModule } from '@angular/material/chips';
import { Ng2SearchPipeModule } from 'ng2-search-filter';
const exampleRoutes: Route[]=[
  {
    path:'',
    component: EmailTemplateComponent
  }
]
@NgModule({
  declarations: [EmailTemplateComponent],
  imports: [
    CommonModule,RouterModule.forChild(exampleRoutes),MatExpansionModule,
    MatFormFieldModule,MatIconModule,ReactiveFormsModule,MatInputModule,
    FormsModule,EmailEditorModule,MatChipsModule,Ng2SearchPipeModule
  ]
})
export class EmailTemplateModule { }
