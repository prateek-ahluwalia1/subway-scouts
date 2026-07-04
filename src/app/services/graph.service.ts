import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { IPublicClientApplication, InteractionRequiredAuthError } from '@azure/msal-browser';
import { MsalService } from '@azure/msal-angular';
import { from, Observable, of, throwError } from 'rxjs';
import { catchError, map, switchMap, tap } from 'rxjs/operators';

@Injectable({
    providedIn: 'root'
})
export class GraphService {
    private graphUrl = 'https://graph.microsoft.com/v1.0';

    constructor(private http: HttpClient, private msalService: MsalService) { }

    createSubscription(): Observable<any> {
        const subscription = {
            changeType: 'created',
            // id: this.global.admin.admin_id,
            notificationUrl: 'https://apis.thescouts.com.au/api/notification',
            resource: 'me/mailFolders(\'inbox\')/messages',
            expirationDateTime: new Date(new Date().getTime() + 3600 * 1000).toISOString(), // 1 hour from now
            clientState: 'secretClientValue'
        };

        return this.getToken().pipe(
            switchMap(token =>
                this.http.post(`${this.graphUrl}/subscriptions`, subscription, {
                    headers: {
                        Authorization: `Bearer ${token}`
                    }
                }).pipe(
                    tap((response: any) => {
                        localStorage.setItem('subscription', JSON.stringify(response));
                    })
                )
            )
        );
    }

    renewSubscription(subscriptionId: string): Observable<any> {
        const expirationDateTime = new Date(new Date().getTime() + 3600 * 1000).toISOString(); // 1 hour from now

        return this.getToken().pipe(
            switchMap(token =>
                this.http.patch(`${this.graphUrl}/subscriptions/${subscriptionId}`, { expirationDateTime }, {
                    headers: {
                        Authorization: `Bearer ${token}`
                    }
                })
            )
        );
    }

    private getToken(): Observable<string> {
        const account = this.msalService.instance.getAllAccounts()[0];

        if (!account) {
            return of(null);
        }

        return from(
            this.msalService.instance.acquireTokenSilent({
                scopes: ['User.Read', 'Mail.Read', 'Mail.ReadWrite', 'Mail.Send'],
                account: account
            })
        ).pipe(
            map(response => response.accessToken),
            catchError(error => {
                if (error instanceof InteractionRequiredAuthError) {
                    return from(
                        this.msalService.instance.acquireTokenPopup({
                            scopes: ['User.Read', 'Mail.Read', 'Mail.ReadWrite', 'Mail.Send']
                        })
                    ).pipe(map(response => response.accessToken));
                }

                return throwError(error);
            })
        );
    }
}
