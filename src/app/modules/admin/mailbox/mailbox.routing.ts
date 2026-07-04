import { ActivatedRouteSnapshot, Route, UrlMatchResult, UrlSegment } from '@angular/router';
import { isEqual } from 'lodash-es';
import { MailboxComponent } from 'app/modules/admin/mailbox/mailbox.component';
import { MailboxFoldersResolver, MailboxMailResolver, MailboxMailsResolver } from 'app/modules/admin/mailbox/mailbox.resolvers';
import { MailboxListComponent } from 'app/modules/admin/mailbox/list/list.component';
import { MailboxDetailsComponent } from 'app/modules/admin/mailbox/details/details.component';
import { MailboxSettingsComponent } from 'app/modules/admin/mailbox/settings/settings.component';
import { MailboxEmptyDetailsComponent } from 'app/modules/admin/mailbox/empty-details/empty-details.component';
import { MsalGuard } from '@azure/msal-angular';

export const mailboxRouteMatcher: (url: UrlSegment[]) => UrlMatchResult = (url: UrlSegment[]) => {

    // Prepare consumed url and positional parameters
    let consumed = url;
    const posParams = {};

    // Settings
    if ( url[0].path === 'settings' )
    {
        // Do not match
        return null;
    }
    // Filter or label
    else if ( url[0].path === 'filter' || url[0].path === 'label' )
    {
        posParams[url[0].path] = url[1];
        posParams['page'] = url[2];

        // Remove the id if exists
        if ( url[3] )
        {
            consumed = url.slice(0, -1);
        }
    }
    // Folder
    else
    {
        posParams['folder'] = url[0];
        posParams['page'] = url[1];
        id: url.length > 2 ? url[2] : null
        // Remove the id if exists
        if ( url[2] )
        {
            consumed = url.slice(0, -1);
        }
    }

    return {
        consumed,
        posParams
    };
};
export const mailboxRunGuardsAndResolvers: (from: ActivatedRouteSnapshot, to: ActivatedRouteSnapshot) => boolean = (from: ActivatedRouteSnapshot, to: ActivatedRouteSnapshot) => {


    // Get the current activated route of the 'from'
    let fromCurrentRoute = from;
    while (fromCurrentRoute.firstChild) {
        fromCurrentRoute = fromCurrentRoute.firstChild;
    }

    // Get the current activated route of the 'to'
    let toCurrentRoute = to;
    while (toCurrentRoute.firstChild) {
        toCurrentRoute = toCurrentRoute.firstChild;
    }

    // Trigger the resolver if the condition met
    if (fromCurrentRoute.paramMap.get('id') && !toCurrentRoute.paramMap.get('id')) {
        return true;
    }

    // If the from and to params are equal, don't trigger the resolver
    const fromParams = {};
    const toParams = {};

    from.paramMap.keys.forEach((key) => {
        fromParams[key] = from.paramMap.get(key);
    });

    to.paramMap.keys.forEach((key) => {
        toParams[key] = to.paramMap.get(key);
    });

    if (isEqual(fromParams, toParams)) {
        return false;
    }

    // Trigger the resolver on other cases
    return true;
};

export const mailboxRoutes: Route[] = [
    {
        path: '',
        redirectTo: 'inbox/1',
        pathMatch: 'full'
    },
    {
        path: ':folder',
        redirectTo: ':folder/1',
        pathMatch: 'full'
    },
    {
        path: '',
        component: MailboxComponent,
        canActivate: [MsalGuard],
        resolve: {
            folders: MailboxFoldersResolver,
        },
        children: [
            {
                component: MailboxListComponent,
                matcher: mailboxRouteMatcher,
                runGuardsAndResolvers: mailboxRunGuardsAndResolvers,
                resolve: {
                    mails: MailboxMailsResolver
                },
                children: [
                    {
                        path: '',
                        pathMatch: 'full',
                        component: MailboxEmptyDetailsComponent
                    },
                    {
                        path: ':id',
                        component: MailboxDetailsComponent,
                        resolve: {
                            mail: MailboxMailResolver
                        }
                    }
                ]
            },
            {
                path: 'settings',
                component: MailboxSettingsComponent
            }
        ]
    }
];
