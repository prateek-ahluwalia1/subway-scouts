import { NgModule } from '@angular/core';
import { RouterModule } from '@angular/router';
import { MatButtonModule } from '@angular/material/button';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatIconModule } from '@angular/material/icon';
import { MatInputModule } from '@angular/material/input';
import { MatRadioModule } from '@angular/material/radio';
import { MatSelectModule } from '@angular/material/select';
import { MatSlideToggleModule } from '@angular/material/slide-toggle';
import { MatSidenavModule } from '@angular/material/sidenav';
import { FuseAlertModule } from '@fuse/components/alert';
import { SharedModule } from 'app/shared/shared.module';
import { SettingsComponent } from 'app/modules/admin/settings/settings.component';
import { SettingsSecurityComponent } from 'app/modules/admin/settings/security/security.component';
import { SettingsPlanBillingComponent } from 'app/modules/admin/settings/plan-billing/plan-billing.component';
import { SettingsNotificationsComponent } from 'app/modules/admin/settings/notifications/notifications.component';
import { SettingsTeamComponent } from 'app/modules/admin/settings/team/team.component';
import { settingsRoutes } from 'app/modules/admin/settings/settings.routing';
import { MaterialModule } from 'app/shared/material.module';
import { AdminModule } from '../admin.module';
import { SupportService } from './support.service';
import { FuseCardModule } from '@fuse/components/card';

@NgModule({
    declarations: [
        SettingsComponent,
        SettingsSecurityComponent,
        SettingsPlanBillingComponent,
        SettingsNotificationsComponent,
        SettingsTeamComponent
    ],
    imports     : [
        RouterModule.forChild(settingsRoutes),
        MatButtonModule,
        MatFormFieldModule,
        MatIconModule,
        MatInputModule,
        MatRadioModule,
        MatSelectModule,
        MatSidenavModule,
        MatSlideToggleModule,
        FuseAlertModule,
        SharedModule,
        MaterialModule,
        AdminModule,
        FuseCardModule
        // SocketIoModule.forRoot(config)
    ],
    providers: [SupportService],
})
export class SettingsModule
{
}
