import { Route } from '@angular/router';
import {EmailVerifyComponent} from 'app/modules/auth/email-verify/email-verify.component';


export const authEmailVerifyRoutes: Route[] = [
    {
        path     : '',
        component: EmailVerifyComponent
    }
];