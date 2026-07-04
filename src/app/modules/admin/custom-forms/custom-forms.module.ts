import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { CustomFormsComponent } from './custom-forms.component';
import { AdminModule } from '../admin.module';
import { CustomFormsRoutingModule } from './custom-forms-routing.module';
import { FormTypesComponent } from './form-types/form-types.component';
import { FormListComponent } from './form-list/form-list.component';
import { FuseDrawerModule } from '@fuse/components/drawer';
import { FormBuilderComponent } from './form-builder/form-builder.component';
import { SignaturePadModule } from 'angular2-signaturepad';
import { SettingsPanelComponent } from './settings-panel/settings-panel.component';
import { ShreFormComponent } from './shre-form/shre-form.component';
import { SubmissionDetailComponent } from './submission-detail/submission-detail.component';
import { TemplatesComponent } from './templates/templates.component';
import { FuseCardModule } from '@fuse/components/card';
import { ImportFormComponent } from './import-form/import-form.component';
import { SingedFormComponent } from './singed-form/singed-form.component';
import { NgxExtendedPdfViewerModule } from 'ngx-extended-pdf-viewer';
import { SignableEditorComponent } from './singed-form/signable-editor/signable-editor.component';
import { AngularDraggableModule } from 'angular2-draggable';

@NgModule({
  declarations: [
    CustomFormsComponent,
    FormTypesComponent,
    FormListComponent,
    FormBuilderComponent,
    SettingsPanelComponent,
    ShreFormComponent,
    SubmissionDetailComponent,
    TemplatesComponent,
    ImportFormComponent,
    SingedFormComponent,
    SignableEditorComponent,
  ],
  imports: [
    CommonModule,
    CustomFormsRoutingModule,
    AdminModule,
    FuseDrawerModule,
    FuseCardModule,
    SignaturePadModule,
    NgxExtendedPdfViewerModule,
    AngularDraggableModule 
  ]
})
export class CustomFormsModule { }
