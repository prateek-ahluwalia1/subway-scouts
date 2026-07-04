import { Route } from '@angular/router';
import { CardComponent } from './card/card.component';
import { AdminComponent } from './admin/admin.component';
import { InternalUsersComponent } from './internal-users/internal-users.component';

export const chatAppRoutes: Route[] = [
    {
        path: '',
        component: CardComponent,
    },
    {
        path: 'admins',
        component: AdminComponent,
    },
    {
        path: 'staff',
        component: InternalUsersComponent,
    },
    {
        path: 'customer',
        component: InternalUsersComponent,
    },
    {
        path: 'contractor',
        component: InternalUsersComponent,
    },
];
