import { HttpClient, HttpHeaders } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { GlobalVariable } from 'app/shared/global';
import { Observable, retry } from 'rxjs';

@Injectable({
  providedIn: 'root'
})

export class ColorSettingsService {

  constructor(private http: HttpClient, private global: GlobalVariable) { }

  headers = new HttpHeaders({
    'AuthorizationToken': 'Bearer ' + localStorage.getItem('accessToken')
  });

  httpOptions = {
    headers: new HttpHeaders({
      'Content-Type': 'application/json',
      'Access-Control-Allow-Origin': '*',
      'AuthorizationToken': 'Bearer ' + localStorage.getItem('accessToken')
    }),
  };

  public saveColor(data): Observable<any> {
    let link = this.global.baseUrl + 'portal-colors';
  
    return this.http.post<any>(link, data, this.httpOptions)
      .pipe(retry(1));
  }
}
