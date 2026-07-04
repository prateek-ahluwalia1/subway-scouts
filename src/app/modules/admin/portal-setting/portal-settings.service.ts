import { HttpClient, HttpHeaders } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { GlobalVariable } from 'app/shared/global';
import { Observable, catchError, retry, throwError } from 'rxjs';

@Injectable({
  providedIn: 'root'
})
export class PortalSettingsService {

  private httpOptions = {
    headers: new HttpHeaders({
      'Content-Type': 'application/json',
      'Access-Control-Allow-Origin': '*',
      'AuthorizationToken': 'Bearer ' + localStorage.getItem('accessToken')
    }),
  };
  constructor(private http: HttpClient, private global: GlobalVariable) { }

  addPh(data): Observable<any> {

    let link = this.global.baseUrl + 'add-public-holiday'
    return this.http.post(link, data, this.httpOptions)
  }

  updatePH(data): Observable<any> {

    let link = this.global.baseUrl + 'update-public-holiday'
    return this.http.post(link, data, this.httpOptions)
  }

  deletePH(data): Observable<any> {

    let link = this.global.baseUrl + 'delete-public-holiday'
    return this.http.post(link, data, this.httpOptions)
  }

  getPH(value): Observable<any> {
    let data = {
      state: value
    }
    let link = this.global.baseUrl + 'get-public-holiday'
    return this.http.post(link, data, this.httpOptions)
      .pipe(retry(1), catchError(this.handleError))
  }

  private handleError(error: any): Observable<never> {
    return throwError(error);
  }
}
