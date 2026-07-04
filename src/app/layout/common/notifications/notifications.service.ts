import { Injectable } from '@angular/core';
import { HttpClient, HttpHeaders } from '@angular/common/http';
import { map, Observable, ReplaySubject, switchMap, take, tap } from 'rxjs';
import { Notification } from 'app/layout/common/notifications/notifications.types';
import { GlobalVariable } from 'app/shared/global';

@Injectable({
    providedIn: 'root'
})

export class NotificationsService {
    
    private _notifications: ReplaySubject<Notification[]> = new ReplaySubject<Notification[]>(1);

    httpOptions = {
        headers: new HttpHeaders({
            'Content-Type': 'application/json',
            'Access-Control-Allow-Origin': '*',
            'AuthorizationToken': 'Bearer ' + localStorage.getItem('accessToken')
        }),
    };

    constructor(private _httpClient: HttpClient, private global: GlobalVariable) { }

    get notifications$(): Observable<any> {
        return this._notifications.asObservable();
    }

    getAll(): Observable<any> {
        const link = this.global.baseUrl + 'get-all-unread-notifications'
        return this._httpClient.get<any>(link, this.httpOptions).pipe(
            tap((notifications) => {
                this._notifications.next(notifications);
            })
        );
    }

    update(id: string): Observable<any> {
        let data = {
            admin_id: this.global.admin.admin_id,
            noti_id: id
        }
        const link = this.global.baseUrl + `read-single-notifications`
        return this._httpClient.post<any>(link, data, this.httpOptions);
    }

    markAllAsRead(): Observable<any> {
        const link = this.global.baseUrl + 'read-notifications'
        return this._httpClient.get<any>(link, this.httpOptions)
    }

    notFication(): Observable<any> {
        var link = this.global.baseUrl + 'unseen-notifications';
        return this._httpClient
            .get<any>(link, this.httpOptions)
    }

}
