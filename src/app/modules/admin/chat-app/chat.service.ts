import { Injectable } from '@angular/core';
import { HttpClient, HttpHeaders } from '@angular/common/http';
import { BehaviorSubject, catchError, filter, forkJoin, map, Observable, of, ReplaySubject, retry, switchMap, take, tap, throwError } from 'rxjs';
import { GlobalVariable } from 'app/shared/global';

@Injectable({
    providedIn: 'root'
})

export class ChatService {
    private _contacts: BehaviorSubject<any> = new BehaviorSubject(null);
    private _previousContact: ReplaySubject<any[]> = new ReplaySubject<any[]>(1);
    private _admins: BehaviorSubject<any> = new BehaviorSubject(null);
    private _chat: BehaviorSubject<any> = new BehaviorSubject(null);

    httpOptions = {
        headers: new HttpHeaders({
            'Content-Type': 'application/json',
            'Access-Control-Allow-Origin': '*',
            'AuthorizationToken': 'Bearer ' + localStorage.getItem('accessToken')
        }),
    };

    constructor(private _httpClient: HttpClient, private global: GlobalVariable) {
    }

    getChats(type): Observable<any> {
        var link
        const id = this.global.admin.admin_id
        if (type == 'customer') {
            link = this.global.baseUrl + `get-previous-history-chat-customer/${id}`;
        }
        else if (type == 'staff') {
            link = this.global.baseUrl + `get-previous-history-chat-staff/${id}`;
        }
        else if (type == 'contractor') {
            link = this.global.baseUrl + `get-previous-history-chat-contractor/${id}`;
        }

        return this._httpClient.get<any>(link, this.httpOptions);
    }

    previousContactAdmin(): Observable<any> {
        const id = this.global.admin.admin_id
        var link = this.global.baseUrl + `get-previous-history-chat-admin/${id}`;
        return this._httpClient.get<any>(link, this.httpOptions).pipe(
            tap((response) => {
                this._previousContact.next(response);
            })
        );;
    }

    get admins$(): Observable<any[]> {
        return this._admins.asObservable();
    }

    get previousContacts$(): Observable<any[]> {
        return this._previousContact.asObservable();
    }

    getUserContacts(type: string): Observable<any> {
        console.log(type);
        let link = '';
        let data: any = {};
        switch (type) {
            case 'staff':
                link = this.global.baseUrl + 'get-all-guards';
                data = {
                    pageIndex: 0,
                    pageSize: 5000,
                    document_type: 'security_license',
                    guard_status: 'active'
                };
                break;
            case 'customer':
                link = this.global.baseUrl + 'get-customers';
                data = {
                    status: 'active',
                };
                break;
            case 'contractor':
                link = this.global.baseUrl + 'get-contractors';
                data = {
                    status: 'active',
                };
                break;
            default:
                break;
        }
        return this.fetchContacts(link, data);
    }

    private fetchContacts(link: string, data: any): Observable<any> {
        return this._httpClient.post<any>(link, data, this.httpOptions).pipe(
            tap((response) => {
                if (Array.isArray(response.data)) {
                    this._contacts.next(response.data);
                } else {
                    console.error(`Invalid response format for ${link}:`, response);
                }
            }),
            catchError((error) => {
                console.error(`Error fetching ${link}:`, error);
                return of({ data: [] }); // Return an empty array or handle the error differently
            })
        );
    }

    getAllAdminsData(): Observable<any> {
        var link = this.global.baseUrl + 'get-all-admins';
        return this._httpClient.get<any>(link, this.httpOptions).pipe(
            tap((response) => {
                this._admins.next(response);
            })
        );
    }

    getSubAdminsData(): Observable<any> {
        let data = {
            status: 'active',
        }
        var link = this.global.baseUrl + 'get-sub-admins';
        return this._httpClient.post<any>(link, data, this.httpOptions).pipe(
            tap((response) => {
                this._contacts.next(response.data);
            })
        );
    }

    getChatById(data): Observable<any> {
        let link
        if (data.type == 'customer') {
            link = this.global.baseUrl + 'get-messages-customer';
        }
        else if (data.type == 'staff') {
            link = this.global.baseUrl + 'get-messages-staff';
        }
        else if (data.type == 'contractor') {
            link = this.global.baseUrl + 'get-messages-contractors';
        }
        return this._httpClient.post<any>(link, data, this.httpOptions)
    }

    getMessageAdmin(data): Observable<any> {
        var link = this.global.baseUrl + 'get-messages-admin';
        return this._httpClient.post<any>(link, data, this.httpOptions)
    }

    resetChat(): void {
        // this._chat.next(null);
    }

    sendMessageToExternal(data): Observable<any> {
        var link
        if (data.type == 'customer') {
            link = this.global.baseUrl + 'send-message-customer';
        }
        else if (data.type == 'staff') {
            link = this.global.baseUrl + 'send-message-staff';
        }
        else if (data.type == 'contractor') {
            link = this.global.baseUrl + 'send-message-contractor';
        }
        return this._httpClient.post<any>(link, data, this.httpOptions)
    }

    sendMessageToAdmin(data): Observable<any> {
        var link = this.global.baseUrl + 'send-message-admin';
        return this._httpClient.post<any>(link, data, this.httpOptions)
    }

    attachments(formData, headers): Observable<any> {
        var link = this.global.baseUrl + 'upload-file';
        return this._httpClient
            .post<any>(link, formData, headers)
            .pipe(retry(1));
    }
}
