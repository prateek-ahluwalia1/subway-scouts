import { Route } from '@angular/router';
import { CanDeactivateFileManagerDetails } from 'app/modules/admin/file-manager/file-manager.guards';
import { FileManagerComponent } from 'app/modules/admin/file-manager/file-manager.component';
import { FileManagerListComponent } from 'app/modules/admin/file-manager/list/list.component';
import { FileManagerDetailsComponent } from 'app/modules/admin/file-manager/details/details.component';
import { FoldersComponent } from './folders/folders.component';
import { DocumentsComponent } from './documents/documents.component';
import { UsersComponent } from './users/users.component';

export const fileManagerRoutes: Route[] = [
    {
        path: '',
        component: FileManagerComponent,
        children: [
            {
                path: '',
                component: UsersComponent,
                children: [
                    {
                        path: 'details/:id',
                        component: FileManagerDetailsComponent,
                        canDeactivate: [CanDeactivateFileManagerDetails]
                    }
                ]
            },
            {
                path: ':userType/:folderId',
                component: FoldersComponent,
                children: [
                    {
                        path: 'details/:id',
                        component: DocumentsComponent,
                        canDeactivate: [CanDeactivateFileManagerDetails]
                    }
                ]
            },
            {
                path: ':userType/:folderName/:id',
                component: DocumentsComponent
            },
            {
                path: ':userType', // Capture the user type in the URL
                component: FileManagerListComponent,
                children: [
                    {
                        path: ':userType/:folderId', // Add this child route to navigate to FoldersComponent
                        component: FoldersComponent
                    }
                ]
            }
        ]
    }
];
