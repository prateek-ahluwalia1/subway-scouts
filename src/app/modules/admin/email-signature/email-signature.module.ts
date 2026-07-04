import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { EmailSignatureComponent } from './email-signature.component';
import { Route, RouterModule } from '@angular/router';
import { MatExpansionModule } from '@angular/material/expansion';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatIconModule } from "@angular/material/icon";
import { MatInputModule } from '@angular/material/input';
import { FormsModule, ReactiveFormsModule } from '@angular/forms';
import { CKEditorModule } from '@ckeditor/ckeditor5-angular';
import { Ng2SearchPipeModule } from 'ng2-search-filter';
import { AdminModule } from '../admin.module';

const exampleRoutes: Route[]=[
  {
    path:'',
    component: EmailSignatureComponent
  }
]

@NgModule({
  declarations: [EmailSignatureComponent],
  imports: [
    CommonModule, RouterModule.forChild(exampleRoutes),AdminModule
  ],
  exports:[]
})
export class EmailSignatureModule { }
