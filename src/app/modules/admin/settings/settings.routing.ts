import { Route } from '@angular/router';
import { SettingsComponent } from 'app/modules/admin/settings/settings.component';

export const settingsRoutes: Route[] = [
    {
        path     : '',
        component: SettingsComponent
    }
];
