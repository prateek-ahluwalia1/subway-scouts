import { Injectable } from '@angular/core';
import { HttpClient, HttpHeaders } from '@angular/common/http';
import { Observable } from 'rxjs';
import { GlobalVariable } from 'app/shared/global';

@Injectable({
    providedIn: 'root', // This makes the service available throughout the app
})
export class SupportService {

    httpOptions = {
        headers: new HttpHeaders({
            'Content-Type': 'application/json',
            'Access-Control-Allow-Origin': '*',
            'AuthorizationToken': 'Bearer ' + localStorage.getItem('accessToken')
        }),
    };
    constructor(private http: HttpClient, private global: GlobalVariable) { }

    // Define methods for interacting with your API
    submitTicket(ticketData: any): Observable<any> {
        return this.http.post<any>(`${this.global.baseUrl}gaurd/open-ticket`, ticketData, this.httpOptions);
    }


    getTickets(data?): Observable<any> {
        return this.http.post<any>(`${this.global.baseUrl}get-tickets`, data, this.httpOptions);
    }

    getChats(data?): Observable<any> {
        return this.http.get<any>(`${this.global.baseUrl}get-tickets-details/${data.id}`, this.httpOptions);
    }

    sendMessage(data?): Observable<any> {
        return this.http.post<any>(`${this.global.baseUrl}reply-ticket`, data, this.httpOptions);
    }

    closeTicket(id): Observable<any> {
        return this.http.get<any>(`${this.global.baseUrl}admin/close-ticket/${id}`, this.httpOptions);
    }
}
