import { NgModule } from '@angular/core';
import { RouterModule } from '@angular/router';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { MatSidenavModule } from '@angular/material/sidenav';
import { MatTooltipModule } from '@angular/material/tooltip';
import { SharedModule } from 'app/shared/shared.module';
import { fileManagerRoutes } from 'app/modules/admin/file-manager/file-manager.routing';
import { FileManagerComponent } from 'app/modules/admin/file-manager/file-manager.component';
import { FileManagerDetailsComponent } from 'app/modules/admin/file-manager/details/details.component';
import { FileManagerListComponent } from 'app/modules/admin/file-manager/list/list.component';
import { FoldersComponent } from './folders/folders.component';
import { DocumentsComponent } from './documents/documents.component';
import { UsersComponent } from './users/users.component';

@NgModule({
    declarations: [
        FileManagerComponent,
        FileManagerDetailsComponent,
        FileManagerListComponent,
        FoldersComponent,
        DocumentsComponent,
        UsersComponent
    ],
    imports     : [
        RouterModule.forChild(fileManagerRoutes),
        MatButtonModule,
        MatIconModule,
        MatSidenavModule,
        MatTooltipModule,
        SharedModule
    ]
})
export class FileManagerModule
{
}
