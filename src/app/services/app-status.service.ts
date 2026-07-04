import { HttpClient, HttpHeaders } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { GlobalVariable } from 'app/shared/global';
import { Observable, retry } from 'rxjs';

@Injectable({
  providedIn: 'root'
})

export class AppStatusService {

  constructor(private http: HttpClient, private global: GlobalVariable) { }

  httpOptions = {
    headers: new HttpHeaders({
      'Content-Type': 'application/json',
      'Access-Control-Allow-Origin': '*',
      'AuthorizationToken': 'Bearer ' + localStorage.getItem('accessToken')
    }),
  };

  public getAppData(): Observable<any> {
    let link = this.global.baseUrl + 'app-status';
    return this.http
      .get<any>(link, this.httpOptions)
      .pipe(retry(1));
  }

  public sendNoti(data): Observable<any> {
    let link = this.global.baseUrl + 'close-app-notification';
    return this.http
      .post<any>(link, data, this.httpOptions)
      .pipe(retry(1));
  }
}
