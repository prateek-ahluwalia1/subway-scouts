import { Injectable } from '@angular/core';
import { HttpClient, HttpHeaders } from '@angular/common/http';
import { map, Observable, ReplaySubject, switchMap, take, tap } from 'rxjs';
import { Message } from 'app/layout/common/messages/messages.types';
import { GlobalVariable } from 'app/shared/global';

@Injectable({
    providedIn: 'root'
})

export class MessagesService {
    
    private _messages: ReplaySubject<Message[]> = new ReplaySubject<Message[]>(1);

    constructor(private _httpClient: HttpClient, private global: GlobalVariable) {}

    httpOptions = {
        headers: new HttpHeaders({
            'Content-Type': 'application/json',
            'Access-Control-Allow-Origin': '*',
            'AuthorizationToken': 'Bearer ' + localStorage.getItem('accessToken')
        }),
    };

    get messages$(): Observable<Message[]> {
        return this._messages.asObservable();
    }

    getAll(): Observable<Message[]> {
        let data = {
            send_by:this.global.admin.chat_type,
            id: this.global.admin.admin_id
        }
        var link = this.global.baseUrl + 'get-all-unread-notifications-chat' ;
        return this._httpClient.post<Message[]>(link,data,this.httpOptions).pipe(
            tap((messages) => {
                this._messages.next(messages);
            })
        );
    }

    create(message: Message): Observable<Message> {
        return this.messages$.pipe(
            take(1),
            switchMap(messages => this._httpClient.post<Message>('api/common/messages', { message }).pipe(
                map((newMessage) => {
                    this._messages.next([...messages, newMessage]);
                    return newMessage;
                })
            ))
        );
    }

    update(id: string): Observable<any> {
        let data = {
            admin_id: this.global.admin.admin_id,
            noti_id: id
        }
        const link = this.global.baseUrl + `read-single-notifications-chat`
        return this._httpClient.post<any>(link, data, this.httpOptions);
    }

    delete(id: string): Observable<boolean> {
        return this.messages$.pipe(
            take(1),
            switchMap(messages => this._httpClient.delete<boolean>('api/common/messages', { params: { id } }).pipe(
                map((isDeleted: boolean) => {
                    const index = messages.findIndex(item => item.id === id);
                    messages.splice(index, 1);
                    this._messages.next(messages);
                    return isDeleted;
                })
            ))
        );
    }

    markAllAsRead(): Observable<any> {
        const id = this.global.admin.admin_id
        const link = this.global.baseUrl + 'read-notifications-chat/'+ `${id}`
        return this._httpClient.get<any>(link, this.httpOptions)
    }

    notFication(): Observable<any> {
        const id = this.global.admin.admin_id
        var link = this.global.baseUrl + 'unseen-notifications-chat/' + `${id}`;
        return this._httpClient.get<any>(link, this.httpOptions)
    }
    
}
