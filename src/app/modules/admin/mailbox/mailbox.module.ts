import { NgModule } from '@angular/core';
import { RouterModule } from '@angular/router';
import { MatButtonModule } from '@angular/material/button';
import { MatCheckboxModule } from '@angular/material/checkbox';
import { MatDialogModule } from '@angular/material/dialog';
import { MatDividerModule } from '@angular/material/divider';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatIconModule } from '@angular/material/icon';
import { MatInputModule } from '@angular/material/input';
import { MatMenuModule } from '@angular/material/menu';
import { MatProgressBarModule } from '@angular/material/progress-bar';
import { MatRippleModule } from '@angular/material/core';
import { MatSelectModule } from '@angular/material/select';
import { MatSidenavModule } from '@angular/material/sidenav';
import { FuseFindByKeyPipeModule } from '@fuse/pipes/find-by-key';
import { FuseNavigationModule } from '@fuse/components/navigation';
import { FuseScrollbarModule } from '@fuse/directives/scrollbar';
import { FuseScrollResetModule } from '@fuse/directives/scroll-reset';
import { SharedModule } from 'app/shared/shared.module';
import { MailboxComponent } from 'app/modules/admin/mailbox/mailbox.component';
import { MailboxComposeComponent } from 'app/modules/admin/mailbox/compose/compose.component';
import { MailboxDetailsComponent } from 'app/modules/admin/mailbox/details/details.component';
import { MailboxEmptyDetailsComponent } from 'app/modules/admin/mailbox/empty-details/empty-details.component';
import { MailboxListComponent } from 'app/modules/admin/mailbox/list/list.component';
import { MailboxSettingsComponent } from 'app/modules/admin/mailbox/settings/settings.component';
import { MailboxSidebarComponent } from 'app/modules/admin/mailbox/sidebar/sidebar.component';
import { mailboxRoutes } from 'app/modules/admin/mailbox/mailbox.routing';
import { AdminModule } from '../admin.module';


@NgModule({
    declarations: [
        MailboxComponent,
        MailboxComposeComponent,
        MailboxDetailsComponent,
        MailboxEmptyDetailsComponent,
        MailboxListComponent,
        MailboxSettingsComponent,
        MailboxSidebarComponent
    ],
    imports: [
        RouterModule.forChild(mailboxRoutes),
        MatButtonModule,
        MatCheckboxModule,
        MatDialogModule,
        MatDividerModule,
        MatFormFieldModule,
        MatIconModule,
        MatInputModule,
        MatMenuModule,
        MatProgressBarModule,
        MatRippleModule,
        MatSelectModule,
        MatSidenavModule,
        FuseFindByKeyPipeModule,
        FuseNavigationModule,
        FuseScrollbarModule,
        FuseScrollResetModule,
        SharedModule,
        AdminModule,
    ],
})
export class MailboxModule {
}
