import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';
import { CustomFormsComponent } from './custom-forms.component';
import { FormTypesComponent } from './form-types/form-types.component';
import { FormListComponent } from './form-list/form-list.component';
import { FormBuilderComponent } from './form-builder/form-builder.component';
import { SubmissionDetailComponent } from './submission-detail/submission-detail.component';
import { TemplatesComponent } from './templates/templates.component';
import { ImportFormComponent } from './import-form/import-form.component';
import { SingedFormComponent } from './singed-form/singed-form.component';
import { SignableEditorComponent } from './singed-form/signable-editor/signable-editor.component';

const routes: Routes = [
  {
    path: '',
    component: CustomFormsComponent,
    children: [
      { path: '', component: FormListComponent },
      { path: 'templates', component: FormListComponent },
      { path: 'form-types', component: FormTypesComponent },
      { path: 'form-builder', component: FormBuilderComponent },
      { path: 'templates-list', component: TemplatesComponent },
      { path: 'form-builder/:id', component: FormBuilderComponent },
      { path: 'submission-detail/:id/:title', component: SubmissionDetailComponent },
      { path: 'form-import', component: ImportFormComponent },
      { path: 'signed-forms', component: SingedFormComponent },
      { path: 'signable-editor', component: SignableEditorComponent }
    ]
  }
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule]
})
export class CustomFormsRoutingModule { }
