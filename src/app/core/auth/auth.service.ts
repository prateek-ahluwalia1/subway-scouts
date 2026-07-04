import { ServiceService } from 'app/services/service.service';
import { GlobalVariable } from 'app/shared/global';
import { Injectable } from '@angular/core';
import { HttpClient, HttpHeaders } from '@angular/common/http';
import { catchError, Observable, of, switchMap, throwError } from 'rxjs';
import { AuthUtils } from 'app/core/auth/auth.utils';
import { user as userData } from 'app/mock-api/common/user/data';

@Injectable()
export class AuthService {
    private _authenticated: boolean = false;
    public _user: any = userData;
    tokenType: any;

    constructor(
        private _httpClient: HttpClient,
        public globals: GlobalVariable,
        public server: ServiceService
    ) {}

    httpOptions = {
        headers: new HttpHeaders({
            'Content-Type': 'application/json',
            'Access-Control-Allow-Origin': '*',
            'AuthorizationToken': 'Bearer ' + localStorage.getItem('accessToken'),
        }),
    };

    set accessToken(token: string) {
        localStorage.setItem('accessToken', token);
    }

    get accessToken(): string {
        return localStorage.getItem('accessToken') ?? '';
    }

    forgotPassword(data): Observable<any> {
        var link = this.globals.baseUrl + 'forgot-password';
        return this._httpClient.post(link, data);
    }

    signIn(credentials): Observable<any> {
        if (this._authenticated) {
            return throwError('User is already logged in.');
        }
        let path = this.globals.baseUrl + 'login';

        return this._httpClient.post<any>(path, credentials)
        .pipe(
            switchMap((response: any) => {
                this._authenticated = true;
                if (response.success) {
                    this.accessToken = response.access_token;
                    localStorage.setItem('admin_id', JSON.stringify(response.admin_id))
                    localStorage.setItem('admin', JSON.stringify(response))
                    localStorage.setItem('role_permissions', JSON.stringify(response.role_permissions))
                }
                else {
                    this._authenticated = false;
                }

                this.tokenType = response.token_type;
                this._user.id = response.admin_id;
                this._user.avatar = response.image;
                this._user.name = response.admin_name;
                this._user.status = 'online';
                this._user.email = response.admin_email;
                return of(response);
            })
        );
    }

    // signInWithBusiness(credentials): Observable<any> {
    //     if (this._authenticated) {
    //         return throwError('User is already logged in.');
    //     }
    //     let path = this.globals.baseUrl + 'login';

    //     return this._httpClient.post<any>(path, credentials)
    //         .pipe(
    //             switchMap((response: any) => {

    //                 this._authenticated = true;
    //                 if (response.success) {
    //                     this.accessToken = response.access_token;
    //                     localStorage.setItem('admin_id', JSON.stringify(response.admin_id))
    //                     localStorage.setItem('business_id', JSON.stringify(response.business.id))
    //                     localStorage.setItem('admin', JSON.stringify(response))
    //                     localStorage.setItem('business', JSON.stringify(response.business))
    //                     localStorage.setItem('role_permissions', JSON.stringify(response.role_permissions))
    //                 }
    //                 else {
    //                     this._authenticated = false;
    //                 }

    //                 this.tokenType = response.token_type;
    //                 this._user.id = response.admin_id;
    //                 this._user.avatar = response.image;
    //                 this._user.name = response.admin_name;
    //                 this._user.status = 'online';
    //                 this._user.email = response.admin_email;
    //                 return of(response);
    //             })
    //         );
    // }

    signInUsingToken(): Observable<boolean> {
        const path = this.globals.baseUrl + 'sign-in-with-token';

        return this._httpClient.post<any>(path, {
            accessToken: this.accessToken,
        }, this.httpOptions).pipe(
            catchError(() => of(false)),
            switchMap((response: any) => {
                if (response.message === 'User not found!') {
                    this.handleUserNotFound();
                } else {
                    this.handleSuccessfulSignIn(response);
                }
                return of(true);
            })
        );
    }

    private handleUserNotFound(): void {
        this._authenticated = false;
        this.signOut();
        this.clearLocalStorage();
    }

    private handleSuccessfulSignIn(response: any): void {
        this._authenticated = true;
        this.globals.admin = JSON.parse(localStorage.getItem('admin'));
        this.handlePortalSetting(response);
        this.setUserProperties(response);
    }

    private handlePortalSetting(response: any): void {
        const colorSettings = JSON.parse(localStorage.getItem('color')) || {};

        const primaryColor = colorSettings.primary_color || '#01A37E';
        const secondaryColor = colorSettings.secondary_color || '#1e293e';
        const lightButtonColor = colorSettings.background_color || '#ddf4ef';
        const pendingShiftsColor = colorSettings.pending_shifts || '#E5E0EB';
        const missedShiftsColor = colorSettings.missed_shifts || '#f38185';
        const rejectedShiftsColor = colorSettings.rejected_shifts || '#d39ca1';
        const uncoveredShiftsColor = colorSettings.uncoverd_shifts || '#d9d991';
        const unpublishShiftsColor = colorSettings.unpublish_shifts || '#8fa3a3';
        const unpublishSiteShiftsColor = colorSettings.unpublish_site_shifts || '#d9a7ec';
        const mockShiftsColor = colorSettings.mock_shifts || '#5dcbcb';
        const completedShiftColor = colorSettings.completed_shift || '#6b9e7d';
        const confirmedShiftColor = colorSettings.confirmed_shift || '#c2f8a5';
        const operationalNotesShiftsColor = colorSettings.operational_notes_shifts || '#D2B48C';
        const publishShift = colorSettings.publish_shifts || '#ebf1dd';

        document.documentElement.style.setProperty("--primary_color", primaryColor);
        document.documentElement.style.setProperty("--color-secondary", secondaryColor);
        document.documentElement.style.setProperty("--color-light-button", lightButtonColor);
        document.documentElement.style.setProperty("--publish_shifts", publishShift);
        document.documentElement.style.setProperty("--pending_shifts", pendingShiftsColor);
        document.documentElement.style.setProperty("--missed_shifts", missedShiftsColor);
        document.documentElement.style.setProperty("--rejected_shifts", rejectedShiftsColor);
        document.documentElement.style.setProperty("--uncoverd_shifts", uncoveredShiftsColor);
        document.documentElement.style.setProperty("--unpublish_shifts", unpublishShiftsColor);
        document.documentElement.style.setProperty("--unpublish_site_shifts", unpublishSiteShiftsColor);
        document.documentElement.style.setProperty("--mock_shifts", mockShiftsColor);
        document.documentElement.style.setProperty("--completed_shift", completedShiftColor);
        document.documentElement.style.setProperty("--confirmed_shift", confirmedShiftColor);
        document.documentElement.style.setProperty("--operational_notes_shifts", operationalNotesShiftsColor);
    }


    private setUserProperties(response: any): void {
        this._user.name = response.admin_name;
        this.tokenType = response.token_type;
        this._user.id = response.admin_id;
        this._user.avatar = response.image;
        this._user.status = 'online';
        this._user.email = response.admin_email;
    }

    private clearLocalStorage(): void {
        const itemsToRemove = [
            'admin', 'business_id', 'business', 'accessToken',
            'admin_id', 'color', 'role_permissions', 'routerId'
        ];

        itemsToRemove.forEach(item => localStorage.removeItem(item));
    }

    signOut(): Observable<any> {
        localStorage.removeItem('accessToken');
        localStorage.clear()
        sessionStorage.clear()
        this._authenticated = false;
        return of(true);
    }


    unlockSession(credentials: { email: string; password: string }): Observable<any> {
        return this._httpClient.post('api/auth/unlock-session', credentials);
    }

    check(): Observable<boolean> {
        if (this._authenticated) {
            return of(true);
        }

        // Check the access token availability
        if (!this.accessToken) {
            return of(false);
        }

        // Check the access token expire date
        if (AuthUtils.isTokenExpired(this.accessToken)) {
            return of(false);
        }

        // If the access token exists and it didn't expire, sign in using it
        return this.signInUsingToken();
    }

    getUserRole(): string {
        const user = JSON.parse(localStorage.getItem('admin'));
        const admin_user_type = user.admin_user_type;
        console.log("Admin Role", admin_user_type);
        return admin_user_type;

    }

    resetPassword(data): Observable<any> {
        var link =
            this.globals.baseUrl + 'forgot-password-step-two';
        return this._httpClient.post(link, data);
    }


    checkOTP(data): Observable<any> {
        var link = this.globals.baseUrl + 'check_2fa_enable';
        return this._httpClient.post(link, data);
    }

}
